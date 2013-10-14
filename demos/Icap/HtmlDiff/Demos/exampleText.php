<?php
    include __DIR__ . '/../../../bootstrap.php';
    
    use Icap\HtmlDiff\HtmlDiff;
?>

<!DOCTYPE html>
<html>
<head>
    <title>HtmlDiff - example with only text comparison</title>
    <link rel="stylesheet" type="text/css" href="css/style1.css"/>
</head>
<body>

<?php
    $html1 = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. 
                Donec ullamcorper est in elit vestibulum cursus. Integer euismod dui vel commodo rutrum. 
                Nam a nulla vel sem ultricies condimentum auctor non est. Vivamus cursus eleifend quam, 
                non sagittis purus placerat non. Ut euismod lorem a ipsum porta sollicitudin. Maecenas quis 
                sem non ante eleifend faucibus non et nulla. Quisque ipsum eros, pellentesque 
                nec lectus in, interdum egestas magna. Ut ut nisl ac sem tempor aliquet et sit amet mauris. 
                Donec volutpat nulla nec convallis pretium. Praesent ac euismod urna, id pretium metus. 
                Integer fermentum nisl sit amet malesuada egestas. Nulla porttitor non ipsum vel 
                hendrerit. Nunc velit lectus, suscipit ut sollicitudin sit amet, aliquet at turpis. 
                Nulla at sodales metus. Cras ullamcorper accumsan metus nec auctor. Ut condimentum, quam 
                non vestibulum porttitor, lacus lacus posuere nisl, ac eleifend tortor leo a arcu. Nulla vestibulum 
                euismod eleifend. In hac habitasse platea dictumst. Sed tempor dui justo, 
                id rutrum nisi bibendum ut. Mauris malesuada metus justo, eu eleifend nisl interdum sed. 
                Nam aliquet, dui et sollicitudin ornare, tortor turpis faucibus est, consequat porta 
                eros odio et ipsum. Proin tincidunt consequat est ut fermentum. Sed quis nunc tellus. ";
    
    $html2 = "Lorem ipsum dolor sit amet cing elit. 
                Donec ullamcorper est in elit vestibulum cursus. Integer euismod dui vel commodo rutrum. 
                Nam a nulla vel sem ultricies condimentum sollicitudin sit amet auctor non est. 
                Vivamus cursus eleifend quam, nisl sit amet malesuada egestas.
                non sagittis purus placerat non. Ut euismod lorem a ipsum porta sollicitudin. Maecenas quis 
                sem non ante eleifend faucibus non et nulla. Quisque ipsum eros, pellentesque 
                nec lectus in, interdum egestas magna. Ut ut nisl ac sem tempor aliquet et sit amet mauris. 
                Donec volutpat nulla nec convallis pretium. Praesent ac euismod urna, id pretium metus. 
                Integer fermentum nisl. Nulla porttitor non ipsum vel 
                hendrerit. Nunc velit lectus, suscipit ut sollicitudin sit amet, aliquet at turpis. 
                Nulla at sodales metus. Cras ullamcorper accumsan metus nec auctor. Ut condimentum, quam 
                non vestibulum porttitor, lacus posuere nisl, ac eleifend tortor leo a arcu. Nulla vestibulum 
                euismod eleifend. In hac habitasse platea dictumst. Sed lacus tempor dui justo, 
                id rutrum nisi bibendum ut. Mauris malesuada metus justo, eu eleifend nisl interdum sed. 
                Nam aliquet, dui et nisi bibendum sollicitudin ornare, tortor turpis faucibus est, consequat porta 
                eros odio et ipsum. Proin ut nisi bibendum fermentum. Sed quis nunc tellus. ";

    $htmlDiff = new HtmlDiff($html1, $html2, true);
    $out = $htmlDiff->outputDiff();
    $modifications = $out->getModifications();    
    echo "<h2>Old html</h2>";
    echo $html1;
    echo "<h2>New html</h2>";
    echo $html2;
    echo "<h2>Compared html</h2>";
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