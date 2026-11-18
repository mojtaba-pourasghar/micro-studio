<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Support\MediaStore;
use App\Support\Watermark;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WatermarkTest extends TestCase
{
    use RefreshDatabase;

    protected function png(int $w, int $h): string
    {
        $im = imagecreatetruecolor($w, $h);
        imagefill($im, 0, 0, imagecolorallocate($im, 40, 120, 200));
        ob_start();
        imagepng($im);

        return ob_get_clean();
    }

    protected function cleanup(string $path): void
    {
        @unlink(public_path($path));
    }

    public function test_large_images_get_the_logo_and_small_ones_do_not(): void
    {
        $big = $this->png(800, 500);
        $this->assertNotSame($big, Watermark::bytes($big));
        $small = $this->png(120, 120);
        $this->assertSame($small, Watermark::bytes($small));
    }

    public function test_uploads_are_stamped_unless_disabled(): void
    {
        $bytes = $this->png(900, 600);
        $stamped = MediaStore::fromContents($bytes, 'scene.png');
        $this->assertNotSame($bytes, file_get_contents(public_path($stamped->path)));

        $logo = MediaStore::fromContents($bytes, 'icon.png', watermark: false);
        $this->assertSame($bytes, file_get_contents(public_path($logo->path)));

        Setting::put('site.watermark', false);
        \Illuminate\Support\Facades\Cache::flush();
        $off = MediaStore::fromContents($bytes, 'off.png');
        $this->assertSame($bytes, file_get_contents(public_path($off->path)));

        foreach ([$stamped, $logo, $off] as $m) {
            $this->cleanup($m->path);
        }
    }
}
