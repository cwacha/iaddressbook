<div class="search">
    <form method="POST" action="<?= $PHP_SELF ?>">
        <table border="0">
            <tr>
                <td valign="top" align="right"> 
                    <input type="text" name="q" size="35" value="<?= $QUERY ?>">
                    <input type="hidden" name="do" value="search">
                    <input type="submit" value="<?= $lang['btn_search']?>">
                </td>
            </tr>
        </table>
    </form>
</div>