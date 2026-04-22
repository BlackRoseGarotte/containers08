<?php
declare(strict_types=1);

class Page {
    private string $templatePath;

    public function __construct(string $template) {
        $this->templatePath = $template;
    }

    public function Render(array $data): string {
        extract($data);
        ob_start();
        include $this->templatePath;
        return ob_get_clean();
    }
}