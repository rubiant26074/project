<?php

namespace Tests\Feature;

use App\Http\Controllers\ProjectProcessChecklistController;
use Tests\TestCase;

class ProjectProcessChecklistControllerDocumentPathTest extends TestCase
{
    public function test_normalize_document_path_keeps_unc_share_host_for_file_uri(): void
    {
        $controller = new ProjectProcessChecklistController();

        $method = new \ReflectionMethod($controller, 'normalizeDocumentPath');
        $method->setAccessible(true);

        $result = $method->invoke($controller, 'file://server/share/Folder/Document.pdf');

        if (DIRECTORY_SEPARATOR === '\\') {
            $this->assertSame('\\\\server\\share\\Folder\\Document.pdf', $result);
            return;
        }

        $this->assertSame('//server/share/Folder/Document.pdf', $result);
    }

    public function test_normalize_document_path_handles_local_drive_file_uri(): void
    {
        $controller = new ProjectProcessChecklistController();

        $method = new \ReflectionMethod($controller, 'normalizeDocumentPath');
        $method->setAccessible(true);

        $result = $method->invoke($controller, 'file:///C:/Users/Budi%20R/Documents/file.pdf');

        if (DIRECTORY_SEPARATOR === '\\') {
            $this->assertSame('C:\\Users\\Budi R\\Documents\\file.pdf', $result);
            return;
        }

        $this->assertSame('/C:/Users/Budi R/Documents/file.pdf', $result);
    }

    public function test_normalize_document_path_handles_local_drive_file_uri_lowercase_c(): void
    {
        $controller = new ProjectProcessChecklistController();

        $method = new \ReflectionMethod($controller, 'normalizeDocumentPath');
        $method->setAccessible(true);

        $result = $method->invoke($controller, 'file:///c:/Users/Budi%20R/Documents/file.pdf');

        if (DIRECTORY_SEPARATOR === '\\') {
            $this->assertSame('c:\\Users\\Budi R\\Documents\\file.pdf', $result);
            return;
        }

        $this->assertSame('/c:/Users/Budi R/Documents/file.pdf', $result);
    }
}
