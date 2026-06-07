<?php
class Paginator {
    private int $totalItems;
    private int $perPage;
    private int $currentPage;

    public function __construct(int $totalItems, int $perPage, int $currentPage) {
        $this->totalItems  = $totalItems;
        $this->perPage     = max(1, $perPage);
        $this->currentPage = max(1, $currentPage);
    }

    public function offset(): int {
        return ($this->currentPage - 1) * $this->perPage;
    }

    public function totalPages(): int {
        return (int) ceil($this->totalItems / $this->perPage);
    }

    public function hasPrev(): bool {
        return $this->currentPage > 1;
    }

    public function hasNext(): bool {
        return $this->currentPage < $this->totalPages();
    }

    public function getCurrentPage(): int {
        return $this->currentPage;
    }

    public function getPerPage(): int {
        return $this->perPage;
    }
}
