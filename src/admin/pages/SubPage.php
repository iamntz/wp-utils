<?php

namespace iamntz\wpUtils\admin\pages;

interface SubPage
{
    public function getSlug(): string;

    public function getTitle(): string;

    public function getCap(): string;

    public function render(): void;
}