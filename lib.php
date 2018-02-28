<?php
// Global Vars
$librarypath =  $argv[1];
$tmdbkey = "";
checkarguments($argv);


function checkarguments($argv)
{
    if ($argv < 2 ){
        echo "Failed! Add a path as argument";
    }
    else{
        generateHTMLCore();
        $librarypath =  $argv[1];
        scandirectory($librarypath);
    }
    generateHTMLEnding();
}

function scandirectory($librarypath)
{
    $files = filelist($librarypath);
    var_dump($files);
    file_put_contents ( 'index.html', '<div class="section" id="Files"><div class="container"><div class="row">', FILE_APPEND);
    
    generatefiles($files);
    file_put_contents ( 'index.html', '</div></div></div>', FILE_APPEND);
    $directory = dir_list($librarypath);
    var_dump($directory);
    generatedirectory($directory);
}
function resultisDirectory($result){
    // grant access to global Api key
    global $tmdbkey;
    // Randomize ID for the javascript toggle 
    // NO GOOD IDEA 
    // Needs rework
    $id = rand();
    


    
    // $result contains full path+filename
    //remove full path
    $directoryname = str_replace( $GLOBALS['librarypath'], '', $result);
    //remove '/' at the end
    $directoryname = str_replace( '/', '', $directoryname);
    
    preg_match('/\([0-9]{4}\)$/', $directoryname, $movieyear);
    // $moviename = preg_replace('/\([0-9]+\)/', '', $directoryname);
    print_r($directoryname);
    
    $moviename = str_replace( ' ' . $movieyear['0'], '', $directoryname);
    $querymovie = str_replace( ' ', '%20', $moviename);
    $querymovie = str_replace( '(', '%28', $querymovie);
    $querymovie = str_replace( ')', '%29', $querymovie);
    $movieyear = str_replace( '(', '', $movieyear['0']);
    $movieyear = str_replace( ')', '', $movieyear);
    print_r($movieyear);
    //sleep so secure api limit https://www.themoviedb.org/faq/api
    usleep(250000);
    echo "https://api.themoviedb.org/3/search/movie?api_key=$tmdbkey&language=de-DE&query=$querymovie&page=1&include_adult=true&year=$movieyear";
    $json = file_get_contents("https://api.themoviedb.org/3/search/movie?api_key=$tmdbkey&language=de-DE&query=$querymovie&page=1&include_adult=true&year=$movieyear");
    $movie = json_decode($json, true);
    $movieposter = $movie['results']['0']['poster_path'];
    //Posts all fetched data to cli. 
    // var_dump($movie);
    // Create html container
    $container= "<div class=\"section\" id=\"$moviename\">
    <div class=\"container\">
    <div class=\"row\">
    <div class=\"col-sm-2\">
    <img src=\"https://image.tmdb.org/t/p/w500$movieposter\" class=\"img-responsive\">
    </div>
    <div class=\"col-sm-10\">
    <h1><b><a href=\"javascript:toggle('$id')\">$moviename</a></b></h1>
    <table width=\"100%\">
    <tr>
    <col width=\"25%\">
    <col width=\"25%\">
    <col width=\"25%\">
    <col width=\"25%\">
        <td width=\"25%\">" . get_dir_size_in_gb($result) . " GB </td>
        <td width=\"25%\">" . $movie['results']['0']['original_title'] . "</td>
        <td width=\"25%\">" . get_dir_size_in_gb($result) . " GB </td>
        <td width=\"25%\">" . $movie['results']['0']['original_title'] . "</td>
    </tr>
    </table>
    <xmp>".$movie['results']['0']['overview'].  "</xmp>
    ";
    // add the created container to the index file
    file_put_contents ( 'index.html', $container, FILE_APPEND);
    
    // scandirectory($result);
    
    // Web Api Usage doesnt support spaces and (). Due to the naming of my files : "Movie (year)" I need do remove these 
    
    
    $closecontainer = "</div>
   
    </div></div></div>";

    file_put_contents ( 'index.html', $closecontainer, FILE_APPEND);
}
function resultisFile($result){
        //remove full path
        $filename = str_replace( $GLOBALS['librarypath'], '', $result);
        //remove '/'
        $filename = str_replace( '/', '', $filename);
    $container = '
    <div class="col-sm-6">
        <h1>'. $filename .' </h1>
        <p> Es handelt sich um eine Datei </p>
    </div>';      
    
    file_put_contents ( 'index.html', $container, FILE_APPEND);
}
function generatedirectory($files){
    foreach ($files as $value) 
    {
            resultisDirectory($value);
    }
}
function generatefiles($files){
    foreach ($files as $value) 
    {
        print $value."\n";
        $isdirectory = is_dir($value);
        if($isdirectory == 1){
            resultisDirectory($value);
        }
        else{
            resultisFile($value);
        }
       
    }
 
}

function filelist($d){
    foreach(array_diff(scandir($d),array('.','..')) as $f)if(is_file($d.'/'.$f))$l[]=$d.$f; 
       return $l; 
}
function dir_list($d){ 
    foreach(array_diff(scandir($d),array('.','..', '.git')) as $f)if(is_dir($d.'/'.$f))$l[]=$d.$f."/"; 
    return $l; 
}
function get_dir_size_in_gb($directory){
    $size = 0;
    $files= glob($directory.'/*');
    foreach($files as $path){
        is_file($path) && $size += filesize($path);
        is_dir($path) && get_dir_size_in_gb($path);
    }
    $size = $size / 1024 / 1024 / 1024;
    $size = round($size, 2);
    return $size;
} 
 

function generateHTMLCore(){
    file_put_contents ( 'index.html', '
    <html>
    <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"
            integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN"
            crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"
            integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q"
            crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"
            integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl"
            crossorigin="anonymous"></script>
            <script type="text/javascript">
  function toggle(id){
    var e = document.getElementById(id);
     
    if (e.style.display == "none"){
       e.style.display = "";
    } else {
       e.style.display = "none";
    }
  }
</script>
    <link href="http://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet"
          type="text/css">
    <link href="necessary_files/style.css" rel="stylesheet" type="text/css">
    </head>
    <body>
    <div class="navbar navbar-default navbar-fixed-top">
        <div class="container">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar-ex-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand"><span>MovieLibray</span></a>
            </div>
            <div class="collapse navbar-collapse" id="navbar-ex-collapse top">
                <ul class="nav navbar-nav navbar-right">
                    <li>
                        <a href="/index.php">Home</a>
                    </li>
                    <li>
                        <a href="/index.php">PH</a>
                    </li>
                    <li>
                    <a href="/index.php">PH</a>
                </li>
                </ul>
            </div>
        </div>
    </div>
    ');
}
function generateHTMLEnding(){
file_put_contents ( 'index.html', '
</div>
</div>
</div>
<footer class="section section-primary">
    <div class="container">
        <div class="row">
            <div class="col-sm-3">
                <h1>MediaLib</h1>
                <p>All infos got scanned and are shown above.
                    <br><br>
                </p>
            </div>
            <div class="col-sm-3">
                <p class="text-info text-right">
                    <br>
                    <br>
                </p>
                <div class="row">
                    <div class="col-md-12 hidden-xs text-right">
                        <a href="https://github.com/J3n50m4t/library-view" target="_blank"><i class="fa fa-3x fa-fw fa-github text-inverse"></i></a>
                        <a href="#top"><i class="fa fa-3x fa-fw fa-arrow-up text-inverse"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>
', FILE_APPEND);
}
?>
    