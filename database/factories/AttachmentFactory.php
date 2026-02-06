<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Attachment>
 */
class AttachmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'filename' => $this->faker->words(3, true) . '.pdf',
            'filepath' => function (array $attributes) {
                // Ensure directory exists
                $path = storage_path('app/public/attachments');
                if (!file_exists($path)) {
                    mkdir($path, 0755, true);
                }

                // Create a dummy PDF file
                $filename = md5(uniqid()) . '.pdf';
                $fullPath = $path . '/' . $filename;

                // Write a simple text file acting as a PDF for demo purposes
                // Real PDF generation needs a library, but for testing file download/existence this is enough
                file_put_contents($fullPath, "%PDF-1.4\n%âãÏÓ\n1 0 obj\n<<\n/Type /Catalog\n/Pages 2 0 R\n>>\nendobj\n2 0 obj\n<<\n/Type /Pages\n/Kids [3 0 R]\n/Count 1\n>>\nendobj\n3 0 obj\n<<\n/Type /Page\n/Parent 2 0 R\n/MediaBox [0 0 612 792]\n/Resources <<\n/Font <<\n/F1 4 0 R\n>>\n>>\n/Contents 5 0 R\n>>\nendobj\n4 0 obj\n<<\n/Type /Font\n/Subtype /Type1\n/BaseFont /Helvetica\n>>\nendobj\n5 0 obj\n<<\n/Length 44\n>>\nstream\nBT\n/F1 24 Tf\n100 700 Td\n(Hello World) Tj\nET\nendstream\nendobj\nxref\n0 6\n0000000000 65535 f \n0000000010 00000 n \n0000000060 00000 n \n0000000117 00000 n \n0000000224 00000 n \n0000000302 00000 n \ntrailer\n<<\n/Size 6\n/Root 1 0 R\n>>\nstartxref\n397\n%%EOF");

                return 'attachments/' . $filename;
            },
            'expiry_date' => $this->faker->optional()->dateTimeBetween('now', '+1 year'),
        ];
    }
}
