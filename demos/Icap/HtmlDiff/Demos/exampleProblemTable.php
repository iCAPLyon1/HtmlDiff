<?php
    include __DIR__ . '/../../../bootstrap.php';
    
    use Icap\HtmlDiff\HtmlDiff;
?>

<!DOCTYPE html>
<html>
<head>
    <title>HtmlDiff - problem with table example</title>
    <link rel="stylesheet" type="text/css" href="css/style2.css"/>
</head>
<body>

<?php
    $html1 = "<p><i>Lorem ipsum</i> dolor <span style='font-style:italic;color:green;'>sit</span> amet, <strong>consectetur</strong> adipiscing elit. <strong>Mauris malesuada metus justo</strong>.</p>
                                    <p>Donec ullamcorper est <img src='images/git-logo.png' height='50'/> in elit <b>vestibulum</b> cursus <a href='http://github.com/iCAPLyon1/HtmlDiff'>here</a>. Note how the link has no tooltip</p>
                                    <p>Table example</p>                                   
                                    <table cellpadding='0' cellspacing='0'>
                                    <tr><td>Cell 1.1 interdum</td><td>Cell 1.2 egestas</td></tr>
                                    <tr><td>Cell 2.1 magna</td><td>Cell 2.2 ipsum</td></tr>
                                    <tr><td>Cell 3.1 faucibus (this row will be deleted)</td><td>Cell 3.2 auctor</td></tr>
                                    </table>
                                    Here is a number 2 32";
    $html2 = "<p>Lorem ipsum dolor sit amet, <strong>text to</strong> consectetur adipiscing <span style='color:red;'>elit</span> mauris <strong>malesuada <u>metus</u> justo</strong>.</p>
                                <p>Donec ullamcorper <i>est</i> in elit vestibulum cursus <a title='Added tooltip' href='http://github.com/iCAPLyon1/HtmlDiff'>here</a>. Note how the link has a tooltip now and the HTML diff algorithm has preserved formatting.</p>
                                <p>New Table example</p>
                                <table cellpadding='0' cellspacing='0'>
                                    <tr><td>Cell 1.1 pellentesque</td><td>Cell 1.2 condimentum</td></tr>
                                    <tr><td>Cell 2.1 volutpat</td><td>Cell 2.2 fermentum</td></tr>
                                </table>
                                Here is a number 2 <sup>32</sup>";

    $htmlDiff = new HtmlDiff($html1, $html2, true);
    $out = $htmlDiff->outputDiff();    
    $modifications = $out->getModifications();    
    echo "<h2>Old html</h2>";
    echo $html1;
    echo "<h2>New html</h2>";
    echo $html2;
    echo "<h2>Compared html (notice that the removed row of the table has disapeared in the compared version)</h2>";
    echo $out->toString();
    echo "<h3>Modifications results:</h3>";
    echo "<p>";
    echo "<span class='diff-html-added'>++ ".$modifications['added']."</span><br/>";
    echo "<span class='diff-html-removed'>-- ".$modifications['removed']."</span><br/>";
    echo "<span class='diff-html-changed'>~~ ".$modifications['changed']."</span>";
    echo "</p>";

?>

</body>
</html>