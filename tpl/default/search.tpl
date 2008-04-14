<div class="search">
    <form method="POST" action="<?= $PHP_SELF ?>" name='search'>
        <input type="hidden" name="do" value="search" />
        <input type="hidden" name="id" value="<?= hsc($ID) ?>" />
        <input type="text" name="q" size="15" value="<?= hsc($QUERY) ?>" class="search_text" />
        <input type="submit" value="<?= $lang['btn_search'] ?>" class="button" />
    </form>
</div>