<?php

namespace App\Support;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class GoogleDriveUploadService
{
    private string $tokenPath = 'google-drive/token.json';
    private ?array $envFallback = null;

    public function authorizationUrl(): string
    {
        $this->ensureConfigured();

        return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
            'client_id' => $this->clientId(),
            'redirect_uri' => $this->redirectUri(),
            'response_type' => 'code',
            'scope' => 'https://www.googleapis.com/auth/drive.file',
            'access_type' => 'offline',
            'prompt' => 'consent',
        ]);
    }

    public function exchangeCode(string $code): void
    {
        $this->ensureConfigured();

        $response = $this->http()->asForm()->post('https://oauth2.googleapis.com/token', [
            'client_id' => $this->clientId(),
            'client_secret' => $this->clientSecret(),
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->redirectUri(),
        ]);

        if (! $response->successful()) {
            throw new RuntimeException('Google Drive belum berhasil terhubung.');
        }

        $payload = $response->json();
        $this->storeToken($payload);
    }

    public function upload(UploadedFile $file, string $namePrefix = ''): array
    {
        $this->ensureConfigured();

        $accessToken = $this->accessToken();
        $filename = $this->buildFilename($file, $namePrefix);

        $metadata = array_filter([
            'name' => $filename,
            'parents' => filled($this->folderId()) ? [$this->folderId()] : null,
        ]);

        $boundary = 'bcp_drive_' . bin2hex(random_bytes(12));
        $body = implode("\r\n", [
            '--' . $boundary,
            'Content-Type: application/json; charset=UTF-8',
            '',
            json_encode($metadata, JSON_THROW_ON_ERROR),
            '--' . $boundary,
            'Content-Type: ' . ($file->getMimeType() ?: 'application/octet-stream'),
            '',
            file_get_contents($file->getRealPath()),
            '--' . $boundary . '--',
            '',
        ]);

        $upload = $this->http()
            ->withToken($accessToken)
            ->withHeaders(['Content-Type' => 'multipart/related; boundary=' . $boundary])
            ->withBody($body)
            ->post('https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart&fields=id,name,webViewLink');

        if (! $upload->successful()) {
            throw new RuntimeException('Dokumen belum berhasil diupload ke Google Drive.');
        }

        $uploadedFile = $upload->json();
        $this->makeFileReadable((string) $uploadedFile['id'], $accessToken);

        return [
            'id' => $uploadedFile['id'],
            'name' => $uploadedFile['name'] ?? $filename,
            'link' => $uploadedFile['webViewLink'] ?? 'https://drive.google.com/file/d/' . $uploadedFile['id'] . '/view',
        ];
    }

    public function isConnected(): bool
    {
        return Storage::disk('local')->exists($this->tokenPath);
    }

    private function accessToken(): string
    {
        $token = $this->storedToken();

        if (! empty($token['access_token']) && ($token['expires_at'] ?? 0) > now()->addMinute()->timestamp) {
            return $token['access_token'];
        }

        if (empty($token['refresh_token'])) {
            throw new RuntimeException('Google Drive belum terhubung. Hubungkan ulang akun Google Drive.');
        }

        $response = $this->http()->asForm()->post('https://oauth2.googleapis.com/token', [
            'client_id' => $this->clientId(),
            'client_secret' => $this->clientSecret(),
            'refresh_token' => $token['refresh_token'],
            'grant_type' => 'refresh_token',
        ]);

        if (! $response->successful()) {
            throw new RuntimeException('Token Google Drive kedaluwarsa. Hubungkan ulang akun Google Drive.');
        }

        $payload = array_merge($token, $response->json());
        $this->storeToken($payload);

        return $payload['access_token'];
    }

    private function makeFileReadable(string $fileId, string $accessToken): void
    {
        $this->http()
            ->withToken($accessToken)
            ->post("https://www.googleapis.com/drive/v3/files/{$fileId}/permissions", [
                'role' => 'reader',
                'type' => 'anyone',
            ]);
    }

    private function storedToken(): array
    {
        if (! $this->isConnected()) {
            throw new RuntimeException('Google Drive belum terhubung.');
        }

        return json_decode(Storage::disk('local')->get($this->tokenPath), true, flags: JSON_THROW_ON_ERROR);
    }

    private function storeToken(array $payload): void
    {
        if (isset($payload['expires_in'])) {
            $payload['expires_at'] = now()->addSeconds((int) $payload['expires_in'])->timestamp;
        }

        Storage::disk('local')->put($this->tokenPath, json_encode($payload, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
    }

    private function clientId(): string
    {
        return $this->credential('client_id', 'GOOGLE_DRIVE_CLIENT_ID');
    }

    private function ensureConfigured(): void
    {
        if (blank($this->clientId()) || blank($this->clientSecret()) || blank($this->redirectUri())) {
            throw new RuntimeException('Konfigurasi Google Drive belum lengkap. Isi GOOGLE_DRIVE_CLIENT_ID, GOOGLE_DRIVE_CLIENT_SECRET, dan GOOGLE_DRIVE_REDIRECT_URI.');
        }
    }

    private function clientSecret(): string
    {
        return $this->credential('client_secret', 'GOOGLE_DRIVE_CLIENT_SECRET');
    }

    private function redirectUri(): string
    {
        return $this->credential('redirect_uri', 'GOOGLE_DRIVE_REDIRECT_URI');
    }

    private function folderId(): ?string
    {
        $folderId = $this->credential('folder_id', 'GOOGLE_DRIVE_FOLDER_ID');

        return filled($folderId) ? $folderId : null;
    }

    private function credential(string $configKey, string $envKey): string
    {
        $configuredValue = (string) config("services.google_drive.{$configKey}");

        if (filled($configuredValue)) {
            return $configuredValue;
        }

        return (string) ($this->envFallback()[$envKey] ?? '');
    }

    private function envFallback(): array
    {
        if ($this->envFallback !== null) {
            return $this->envFallback;
        }

        $path = base_path('.env');
        if (! is_file($path) || ! is_readable($path)) {
            return $this->envFallback = [];
        }

        $values = [];
        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#') || ! str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $values[trim($key)] = trim(trim($value), "\"'");
        }

        return $this->envFallback = $values;
    }

    private function buildFilename(UploadedFile $file, string $namePrefix): string
    {
        $prefix = trim(preg_replace('/[^A-Za-z0-9._ -]+/', '-', $namePrefix), '- ');
        $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();
        $safeName = trim(preg_replace('/[^A-Za-z0-9._ -]+/', '-', $name), '- ') ?: 'dokumen';

        return trim($prefix . ' ' . now()->format('Ymd-His') . ' ' . $safeName) . ($extension ? ".{$extension}" : '');
    }

    private function http(): PendingRequest
    {
        return Http::timeout(60)->acceptJson();
    }
}
