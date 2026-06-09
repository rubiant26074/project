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
}
