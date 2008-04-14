<div class="search">
    <div id="applesearch">
        <span class="sbox_l"></span>
        <span class="sbox">
            <form method="POST" action="<?= $PHP_SELF ?>" name='search'>
                <input type="hidden" name="do" value="search" />
                <input type="hidden" name="id" value="<?= $ID ?>" />
                <input type="search" name="q"  value="<?= $QUERY ?>" id="srch_fld" placeholder="<?= $lang['btn_search'] ?>" autosave="applestyle_srch" results="5" onkeyup="applesearch.onChange('srch_fld','srch_clear')" />
            </form>
        </span>
        <span class="sbox_r" id="srch_clear"></span>
    </div>
</div>

