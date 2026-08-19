<?php

declare(strict_types=1);

namespace App\Domain\Entities;

class Survey
{
    private ?int $id;
    private string $username;
    private ?string $visit;
    private ?string $site;
    private ?string $resource;
    private ?string $nation;
    private ?string $bali;
    private ?string $visitTime;
    private ?string $news;
    private ?string $datetime;

    public function __construct(
        string $username,
        ?string $visit = null,
        ?string $site = null,
        ?string $resource = null,
        ?string $nation = null,
        ?string $bali = null,
        ?string $visitTime = null,
        ?string $news = null,
        ?string $datetime = null,
        ?int $id = null
    ) {
        $this->username = $username;
        $this->visit = $visit;
        $this->site = $site;
        $this->resource = $resource;
        $this->nation = $nation;
        $this->bali = $bali;
        $this->visitTime = $visitTime;
        $this->news = $news;
        $this->datetime = $datetime;
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getVisit(): ?string
    {
        return $this->visit;
    }

    public function getSite(): ?string
    {
        return $this->site;
    }

    public function getResource(): ?string
    {
        return $this->resource;
    }

    public function getNation(): ?string
    {
        return $this->nation;
    }

    public function getBali(): ?string
    {
        return $this->bali;
    }

    public function getVisitTime(): ?string
    {
        return $this->visitTime;
    }

    public function getNews(): ?string
    {
        return $this->news;
    }

    public function getDatetime(): ?string
    {
        return $this->datetime;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'visit' => $this->visit,
            'site' => $this->site,
            'resource' => $this->resource,
            'nation' => $this->nation,
            'bali' => $this->bali,
            'visit_time' => $this->visitTime,
            'news' => $this->news,
            'datetime' => $this->datetime,
        ];
    }
}
