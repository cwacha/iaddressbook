<div class="row" style="padding-top: 50em;"></div>
<div id="debug" style="z-index: 1020; position: relative; background: white;">
    <h2>Debug Data</h2>
    <table class="table">
        <tr><th>Key</th><th>Value</th></tr>
        <tr><td>baseurl</td><td><?php echo $baseurl ?></td></tr>
        <tr><td>baseuri</td><td><?php echo $baseuri ?></td></tr>
        <tr><td>webappuri</td><td><?php echo $webappuri ?></td></tr>
        <tr><td>request_uri</td><td><?php echo strtolower($_SERVER['REQUEST_URI']) ?></td></tr>
        <tr><td></td><td></td></tr>

        <tr><td>basedir</td><td><?php echo $basedir ?></td></tr>
        <tr><td>tpldir</td><td><?php echo $tpldir ?></td></tr>
        <tr><td>viewname</td><td><?php echo $viewname ?></td></tr>
        <tr><td>viewdocument</td><td><?php echo $viewdocument ?></td></tr>
        <tr><td></td><td></td></tr>

        <tr><td>action</td><td><?php echo $action ?></td></tr>
        <tr><td>ID</td><td><?php echo $ID ?></td></tr>
        <tr><td>QUERY</td><td><?php echo $QUERY ?></td></tr>
        <tr><td>CAT_ID</td><td><?php echo $CAT_ID ?></td></tr>
        <tr><td>contactlist_offset</td><td><?php echo $contactlist_offset ?></td></tr>
        <tr><td>contactlist_limit</td><td><?php echo $contactlist_limit ?></td></tr>
        <tr><td>contactlist_letter</td><td><?php echo $contactlist_letter ?></td></tr>

    </table>


    <pre>
        <code>
$_REQUEST: <?php echo print_r($request, true) ?><br/>

$_SESSION: <?php echo print_r($_SESSION, true) ?><br/>

$_SERVER: <?php echo print_r($_SERVER, true) ?><br/>

$contact: <?php echo print_r($contact, true) ?><br/>

$categories: <?php echo print_r($categories, true) ?><br/>

$AB: <?php echo print_r($AB, true) ?><br/>

$CAT: <?php echo print_r($CAT, true) ?><br/>

$contactlist: (very large)<?php // echo print_r($contactlist, true) ?><br/>

$conf: <?php echo print_r($conf, true) ?><br/>

$lang: <?php echo print_r($lang, true) ?><br/>

        </code>
    </pre>
</div>