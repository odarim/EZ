<?php

namespace App\Twig\Components\Trait;

use Symfony\UX\LiveComponent\ComponentToolsTrait;

trait ToastActionTrait
{
    use ComponentToolsTrait;

    public function showToast(string $type, string $text, ?string $title = null, array $options = []): void
    {
        $this->emit('toast:show', [
            'type' => $type,
            'text' => $text,
            'title' => $title,
            'options' => $options
        ]);
    }

    public function toastSuccess(string $text, ?string $title = null, array $options = []): void
    {
        $this->showToast('success', $text, $title, $options);
    }

    public function toastError(string $text, ?string $title = null, array $options = []): void
    {
        $this->showToast('error', $text, $title, $options);
    }

    public function toastInfo(string $text, ?string $title = null, array $options = []): void
    {
        $this->showToast('info', $text, $title, $options);
    }

    public function toastWarning(string $text, ?string $title = null, array $options = []): void
    {
        $this->showToast('warning', $text, $title, $options);
    }
}
