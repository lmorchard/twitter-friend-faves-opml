<?php
    $user_name = 'lmorchard';
    $password = 'not my password';
    $page_title = "Twitter friends' favorites feeds as OPML";

    $base_url = $_SERVER['REQUEST_URI'];
    function e($str) {
        echo htmlspecialchars($str);
    }
?>
<?php if (empty($_GET['screenname'])): ?>
<html>
    <head>
        <title><?php e($page_title) ?></title>
        <style type="text/css">
            form { }
            form fieldset { }
            form fieldset legend { }
            form fieldset ul { list-style-type: none }
            form fieldset ul li { padding-bottom: 0.5em }
            form fieldset ul li label, form fieldset ul li span 
                { float: left; text-align: right; width: 12ex; padding-right: 0.25em; }
            form fieldset ul li label:after { content: ":"; }
            form fieldset ul li input { }
        </style>
    </head>
    <body>
        <h1><?php e($page_title) ?></h1>
        <form method="GET">
            <fieldset>
                <legend>user details</legend>
                <ul>
                    <li>
                        <label>your screen name</label>
                        <input name="screenname" type="text" />
                    </li>
                    <li>
                        <span>&nbsp;</span>
                        <input type="submit" />
                    </li>
                </ul>
            </fieldset>
        </form>
        <p>
            Written by <a href="http://decafbad.com/">l.m.orchard</a>.  Share and enjoy.
            Download the source for this utility from 
            <a href="http://github.com/lmorchard/twitter-friend-faves-opml/">GitHub</a>.
        </p>
    </body>
</html>
<?php else: ?>
<?php
    $screenname = $_GET['screenname'];
    $ch = curl_init('http://twitter.com/statuses/friends/'.$screenname.'.json');
    curl_setopt_array($ch, array(
        CURLOPT_USERAGENT      => 'twitter-friend-faves-opml/0.1',
        CURLOPT_FAILONERROR    => true,
        CURLOPT_RETURNTRANSFER => true,
        // CURLOPT_USERPWD        => $user_name . ':' . $password
    ));
    $resp = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    $friends = json_decode($resp);

    // Start off the OPML document with an XmlWriter.
    require_once 'lib/XmlWriter.php';
    $x = new Memex_XmlWriter(array(
        'parents' => array( 'opml', 'head', 'body', 'outline')
    ));
    $title = "$screenname's friends' twitter favorites";
    $x->opml()
        ->head()
            ->title($title)
        ->pop()
        ->body()
        ->outline(array('title'=>$title));

    foreach ($friends as $friend) {
        $x->emptyelement('outline', array(
            'type'    => 'rss',
            'version' => 'atom',
            'title'   => $friend->name . "'s twitter favorites",
            'xmlUrl'  => 'http://twitter.com/favorites/'.$friend->screen_name.'.atom'
        ));
    }

    $x->pop();

    header('Content-Type: text/xml+opml');
    echo $x->getXML();
?>
<?php endif ?>
