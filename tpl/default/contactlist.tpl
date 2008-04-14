        <td class="contactlist_td">
            <div class="contactlist">
                <div class="panel">
                    <table class="header">
                        <tr class="header_tr">
                            <td class="endcap"><img src="<?= AB_TPL ?>images/split1_left.gif"></td>
                            <td class="middle"><?= $lang['contacts'] ?> (<?= count($contactlist) ?>)</td>
                            <td class="endcap"><img src="<?= AB_TPL ?>images/split1_right.gif"></td>
                        </tr>
                    </table>
                </div>
                <?php tpl_contactlist() ?>

                <div class="separator100">&nbsp;</div>
                <div class="contactlist_footer">
                    <form method="POST" action="<?= $PHP_SELF ?>">
                        <input type="hidden" name="do" value="new" />
                        <input type="submit" value="<?= $lang['btn_new'] ?>" class="button" style="float: right;" />
                    </form>             
                    <!-- Begin Export VCards -->
                    <form method="POST" action="<?= $PHP_SELF ?>">
                        <input type="hidden" name="do" value="export_vcard_cat" />
                        <input type="hidden" name="id" value="<?= $contact->id ?>" />
                        <input type="submit" value="<?= $lang['btn_vcardexport_cat']?>" class="button" style="float: right;" />
                    </form>
                    <!-- End Export VCards -->
                </div>
            </div>
        </td>
