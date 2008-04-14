<div class="search">
    <form method="POST" action="<?= $PHP_SELF ?>">
        <input type="hidden" name="do" value="search" />
        <input type="text" name="q" size="15" value="<?= $QUERY ?>" class="search_text" />
        <input type="submit" value="<?= $lang['btn_search'] ?>" class="button" />
    </form>
</div>