<?php

///////////////////////
//    G R O U P S    //
///////////////////////

$roles['all']    = 'login,logout,reset,'.
                   'show,img,search,select_letter,select_offset,'.
                   'edit,new,save,delete,delete_many,'.
                   'cat_select,'.
                   'cat_add,cat_del,cat_del_empty,cat_add_contacts,cat_del_contacts,'.
                   'import_vcard,import_folder,'.
                   'export_vcard,export_vcard_cat,export_csv_cat,export_ldif_cat,'.
                   'account_mypassword,'.
                   'account_save,account_password,account_delete,'.
                   'role_save,role_delete,'.
                   'config_save,'.
                   'language_save,language_delete,'.
                   'debug,check,info,'.
                   '/home,/export,/profile,/profile/password,'.
                   '/admin,'.
                   '/admin/config,'.
                   '/admin/accounts,/admin/account,/admin/account/edit,/admin/account/password,'.
                   '/admin/roles,/admin/role/edit,'.
                   '/admin/translator,/admin/translator/edit';

$roles['admin']  = 'login,logout,reset,'.
                   'show,img,search,select_letter,select_offset,'.
                   'edit,new,save,delete,delete_many,'.
                   'cat_select,'.
                   'cat_add,cat_del,cat_del_empty,cat_add_contacts,cat_del_contacts,'.
                   'import_vcard,import_folder,'.
                   'export_vcard,export_vcard_cat,export_csv_cat,export_ldif_cat,'.
                   'account_mypassword,'.
                   'account_save,account_password,account_delete,'.
                   'role_save,role_delete,'.
                   'config_save,'.
                   'language_save,language_delete,'.
                   'debug,check,info,'.
                   '/home,/export,/profile,/profile/password,'.
                   '/admin,'.
                   '/admin/config,'.
                   '/admin/accounts,/admin/account,/admin/account/edit,/admin/account/password,'.
                   '/admin/roles,/admin/role/edit,'.
                   '/admin/translator,/admin/translator/edit';

$roles['editor'] = 'login,logout,reset,'.
                   'show,img,search,select_letter,select_offset,'.
                   'edit,new,save,delete,delete_many,'.
                   'cat_select,'.
                   'cat_add,cat_del,cat_del_empty,cat_add_contacts,cat_del_contacts,'.
                   'import_vcard,import_folder,'.
                   'export_vcard,export_vcard_cat,export_csv_cat,export_ldif_cat,'.
                   'account_mypassword,'.
                   '/home,/export,/profile,/profile/password';

$roles['guest']  = 'login,logout,reset,'.
                   'show,img,search,select_letter,select_offset,'.
                   'cat_select,'.
                   'export_vcard,export_vcard_cat,export_csv_cat,export_ldif_cat,'.
                   '/home,/export,/profile,/profile/password';

?>
