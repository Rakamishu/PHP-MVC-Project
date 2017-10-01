<?php

namespace App\Models;

class Pagination 
{
    
    protected $db;    
    private $currentPage;
    private $perPage;
    private $total;
    private $table;
    
    public function __construct(string $table, int $currentPage = 1, int $perPage)
    {
        $this->currentPage = $currentPage;
        $this->perPage = $perPage;
        $this->table = $table;
        
        $this->db = \App\Core\Database::getInstance();
        $getTotalRows = $this->db->getRows("SELECT COUNT(*) as rows FROM $this->table");

        $getTotalRows = $getTotalRows[0]->rows;
        $this->total = ceil($getTotalRows/$this->perPage);
    }
    
    public function createLinks(string $url) 
    {
        $adjacents = 2;
        $next_page = $this->currentPage + 1;
        $previous_page = $this->currentPage - 1;
        
        $html = '';

        if($this->total > 1)
        {
            $html .= '';
            if ($this->currentPage > 1)
            {
                $html.= '<li><a href="'.$url.''.'1"><i class="fa fa-angle-double-left" aria-hidden="true"></i></a></li>';
                $html.= '<li><a href="'.$url.''.$previous_page.'"><i class="fa fa-angle-left" aria-hidden="true"></i></a></li>';
            } 
            else 
            {
                $html.= '';	
            }
            if ($this->total < 7 + ($adjacents * 2))
            {	
                for ($i = 1; $i <= $this->total; $i++)
                {
                    if ($i == $this->currentPage)
                    {
                        $html.= '<li class="active"><a href="#">'.$i.'</a></li>';
                    } else {
                        $html.= '<li><a href="'.$url.''.$i.'">'.$i.'</a></li>';
                    }
                }
            } 
            elseif ($this->total > 5 + ($adjacents * 2))
            {
                if($this->currentPage < 1 + ($adjacents * 2))
                {
                    for ($i = 1; $i < 4 + ($adjacents * 2); $i++)
                    {
                        if ($i == $this->currentPage)
                        {
                            $html.= '<li class="active"><a href="#">'.$i.'</a></li>';
                        } 
                        else 
                        {
                            $html.= '<li><a href="'.$url.''.$i.'">'.$i.'</a></li>';
                        }
                    }
                    $html.= '<li><a href="#">...</a></li>';
                    $html.= '<li><a href="'.$url.''.$this->total.'">'.$this->total.'</a></li>';		
                } 
                elseif ($this->total - ($adjacents * 2) > $this->currentPage && $this->currentPage > ($adjacents * 2))
                {
                    $html.= '<li><a href="'.$url.''.'1">1</a></li>';
                    $html.= '<li><a href="#">...</a></li>';
                    for ($i = $this->currentPage - $adjacents; $i <= $this->currentPage + $adjacents; $i++)
                    {
                        if ($i == $this->currentPage)
                        {
                            $html.= '<li class="active"><a href="#">'.$i.'</a></li>';
                        } 
                        else 
                        {
                            $html.= '<li><a href="'.$url.''.$i.'">'.$i.'</a></li>';	
                        }
                    }
                    $html.= '<li><a href="#">...</a></li>';
                    $html.= '<li><a href="'.$url.''.$this->total.'">'.$this->total.'</a></li>';		
                } 
                else 
                {
                    $html.= '<li><a href="'.$url.''.'1">1</a></li>';
                    $html.= '<li><a href="#">...</a></li>';
                    for($i = $this->total - (2 + ($adjacents * 2)); $i <= $this->total; $i++)
                    {
                        if ($i == $this->currentPage)
                        { 
                            $html.= '<li class="active"><a href="#">'.$i.'</a></li>';
                        } 
                        else 
                        {
                            $html.= '<li><a href="'.$url.''.$i.'">'.$i.'</a></li>';
                        }
                    }
                }
            }
            if ($this->currentPage < $i - 1)
            { 
                $html.= '<li><a href="'.$url.''.$next_page.'"><i class="fa fa-angle-right" aria-hidden="true"></i></a></li>';
            } 
            else 
            {
                $html.= '';
            }
            if($this->currentPage < $this->total)
            {
                $html.= '<li><a href="'.$url.''.$this->total.'"><i class="fa fa-angle-double-right" aria-hidden="true"></i></a></li>';
            } else {
                $html.= '';	
            }
        }

        return $html;
    }
    
}
