<div class="search">
    <form method="POST" action="<?= $PHP_SELF ?>">
    
    <table border="0" cellspacing="2" width="380">
        <tr>
            <td valign="top"> 
                <input type="text" name="q" size="35" value="<?= $QUERY ?>">
            </td>
        </tr>
        <tr>
            <td>
            <input type="hidden" name="do" value="search">
            <input type="submit" value="Submit">
            </td>
            <td>&nbsp;</td>
        </tr>
    </table>
    </form>
</div>