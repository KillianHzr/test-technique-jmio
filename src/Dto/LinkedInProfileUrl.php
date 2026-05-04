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
        try {
            $baseUrl = 'linkedin.com/in/';
            $linkedInCom = strpos($rawUrl, $baseUrl);
          
            if ($linkedInCom === false || str_ends_with($rawUrl, ":")) {
                throw new \Exception("No linkedin.com/in/ pattern found");
            }

            $cleanUrl = substr($rawUrl, $linkedInCom, strlen($baseUrl));
            $profilePart = substr($rawUrl, $linkedInCom+strlen($baseUrl));

            if (empty($profilePart)) {
                throw new \Exception("No profile part pattern found");
            }
            for ($i = 0; $i < strlen($profilePart); $i++) {
                if ($profilePart[$i] === '/' || $profilePart[$i] === '?') {
                    break;
                }
                $cleanUrl .= $profilePart[$i];
            }

            if (!empty($cleanUrl)) {
                $this->cleanUrl = urldecode($cleanUrl);
            }
        } catch (\Exception $exception) {
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