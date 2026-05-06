<?php

namespace App\Tests\Unit;

use App\Dto\LinkedInProfileUrl;
use PHPUnit\Framework\TestCase;

class LinkedInProfileUrlTest extends TestCase
{
    /**
     * @dataProvider urlProvider
     */
    public function testUrlCleaning(string $input, ?string $expected): void
    {
        $dto = new LinkedInProfileUrl($input);
        $this->assertEquals($expected, $dto->getNormalizedUrl());
        if ($expected !== null) {
            $this->assertTrue($dto->isValid());
        } else {
            $this->assertFalse($dto->isValid());
        }
    }

    public function urlProvider(): array
    {
        return [
            'Simple URL' => ['https://www.linkedin.com/in/jean-michel', 'linkedin.com/in/jean-michel'],
            'With trailing slash' => ['https://linkedin.com/in/jean-michel/', 'linkedin.com/in/jean-michel'],
            'With query params' => ['https://www.linkedin.com/in/jean-michel?utm_source=share', 'linkedin.com/in/jean-michel'],
            'HTTP instead of HTTPS' => ['http://linkedin.com/in/jean-michel', 'linkedin.com/in/jean-michel'],
            'Without www' => ['https://linkedin.com/in/jean-michel', 'linkedin.com/in/jean-michel'],
            'With special characters' => ['https://www.linkedin.com/in/jean%20michel', 'linkedin.com/in/jean%20michel'],
            'Dashes and underscores' => ['https://www.linkedin.com/in/jean_michel-123', 'linkedin.com/in/jean_michel-123'],
            'Invalid URL' => ['https://google.com', null],
            'Empty string' => ['', null],
            'Garbage prefix' => ['click here: https://www.linkedin.com/in/jm', 'linkedin.com/in/jm'],
        ];
    }
}
