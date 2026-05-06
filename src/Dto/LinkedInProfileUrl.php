<?php

namespace App\Dto;

class LinkedInProfileUrl
{
    private ?string $cleanUrl;

    public function __construct(?string $rawUrl)
    {
        $this->cleanUrl = null;
        if ($rawUrl !== null) {
            $this->setCleanUrl($rawUrl);
        }
    }

    private function setCleanUrl(string $rawUrl): void
    {
        if (preg_match('#linkedin\.com/in/([a-zA-Z0-9\-_%]+)#', $rawUrl, $matches)) {
            $this->cleanUrl = 'linkedin.com/in/' . $matches[1];
        } else {
            $this->cleanUrl = null;
        }
    }

    public function isValid(): bool
    {
        return $this->cleanUrl !== null;
    }

    public function __toString(): string
    {
        return $this->getNormalizedUrl() ?? '';
    }

    public function getNormalizedUrl(): ?string
    {
        return $this->cleanUrl;
    }
}