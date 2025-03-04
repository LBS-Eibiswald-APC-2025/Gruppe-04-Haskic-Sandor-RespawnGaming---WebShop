<?php

class Pagination
{
    private $totalItems;
    private $currentPage;
    private $itemsPerPage;
    private $totalPages;

    public function __construct($totalItems, $currentPage, $itemsPerPage)
    {
        $this->totalItems   = $totalItems;
        $this->currentPage  = max(1, $currentPage);
        $this->itemsPerPage = $itemsPerPage;
        $this->totalPages   = ceil($this->totalItems / $this->itemsPerPage);
    }

    public function createLinks(): string
    {
        if ($this->totalPages <= 1) {
            return '';
        }

        $html = '<nav><ul class="pagination justify-content-center">';

        // Prev
        if ($this->currentPage <= 1) {
            $html .= '<li class="page-item disabled"><a class="page-link" href="#">&laquo;</a></li>';
        } else {
            $prevPage = $this->currentPage - 1;
            $html .= '<li class="page-item"><a class="page-link" href="?page=' . $prevPage . '">&laquo;</a></li>';
        }

        // Seitenlinks
        for ($i = 1; $i <= $this->totalPages; $i++) {
            $active = ($i == $this->currentPage) ? ' active' : '';
            $html .= '<li class="page-item' . $active . '"><a class="page-link" href="?page=' . $i . '">' . $i . '</a></li>';
        }

        // Next
        if ($this->currentPage >= $this->totalPages) {
            $html .= '<li class="page-item disabled"><a class="page-link" href="#">&raquo;</a></li>';
        } else {
            $nextPage = $this->currentPage + 1;
            $html .= '<li class="page-item"><a class="page-link" href="?page=' . $nextPage . '">&raquo;</a></li>';
        }

        $html .= '</ul></nav>';
        return $html;
    }
}
