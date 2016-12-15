<?php
namespace Iekadou\Quickies;

class Pagination
{
    private $pages = array();
    private $page, $page_count, $page_size, $page_offset, $query_offset;
    public function __construct($page = 1, $obj_count = 1, $page_size = 30, $page_offset=2, $url='../%s/')
    {
        $this->page = $page;
        $this->page_size = $page_size;
        $this->page_count = ceil($obj_count/$this->page_size);
        $this->page_offset = $page_offset;
        $this->url = $url;

        if ($this->page > $this->page_count) { $this->page = $this->page_count; }
        if ($this->page >= $this->page_offset+2) {
            array_push($this->pages, 1);
            if ($this->page >= $this->page_offset+3) {
                array_push($this->pages, '...');
            }
        }
        for ($i = $this->page-$this->page_offset; $i <= $this->page+$this->page_offset; $i++) {
            if ($i >0 && $i<=$this->page_count) {
                array_push($this->pages, $i);
            }
        }
        if ($this->page <= $this->page_count-$this->page_offset-1) {
            if ($this->page <= $this->page_count-$this->page_offset-2) {
                array_push($this->pages, '...');
            }
            array_push($this->pages, $this->page_count);
        }
        $this->query_offset = ($this->page-1) * $this->page_size;
    }

    public function render() { ob_start();
        ?>
        <ul class="pagination">
        <?php if ($this->page==1) { ?>
            <li class="disabled"><a href="#"><span class="fa fa-angle-left"></span>&nbsp;prev</a></li>
        <?php } else { ?>
            <li><a href="<?php echo sprintf($this->url, ($this->page-1)); ?>"><span class="fa fa-angle-left"></span>&nbsp;prev</a></li>
        <?php } 
        foreach($this->pages as $page_i) {
            echo '<li';
            if ('...' == $page_i) {
                echo ' class="disabled"><a href="#"';
            } else {
                if ($this->page == $page_i) {
                    echo ' class="active"';
                }
                echo '><a href="'.sprintf($this->url, ($page_i)).'"';
            }
            echo '>'.$page_i.'</a></li>';
        } 
        if ($this->page==$this->page_count) { ?>
            <li class="disabled"><a href="#">next&nbsp;<span class="fa fa-angle-right"></span></a></li>
        <?php } else { ?>
            <li><a href="<?php echo sprintf($this->url, ($this->page+1)); ?>">next&nbsp;<span class="fa fa-angle-right"></span></a></li>
        <?php } ?>
        </ul>
    <?php
        $return = ob_get_contents();
        ob_end_clean();
        return $return;
    }

    public function get_query_offset() {
        return $this->query_offset;
    }

    public function get_page_size() {
        return $this->page_size;
    }
}
