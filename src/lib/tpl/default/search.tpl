<div class="search">
    <div id="applesearch">
        <span class="sbox_l"></span>
        <span class="sbox">
            <form method="POST" action="<?php echo $PHP_SELF; ?>" name='search'>
                <input type="hidden" name="do" value="search" />
                <input type="hidden" name="id" value="<?php echo $ID; ?>" />
                <input type="search" name="q"  value="<?php echo $QUERY; ?>" id="srch_fld" placeholder="<?php echo $lang['btn_search']; ?>" autosave="applestyle_srch" results="5" onkeyup="applesearch.onChange('srch_fld','srch_clear')" />
            </form>
        </span>
        <span class="sbox_r" id="srch_clear"></span>
    </div>
</div>

