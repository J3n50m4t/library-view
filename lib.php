<?php
// Global Vars
$librarypath =  $argv[1];
$tmdbkey = "";
unlink("necessary_files/javascript.js");
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
  
    // $result contains full path+filename
    //remove full path
    $directoryname = str_replace( $GLOBALS['librarypath'], '', $result);
    //remove '/' at the end
    $directoryname = str_replace( '/', '', $directoryname);
    
    preg_match('/\([0-9]{4}\)$/', $directoryname, $movieyear);
    preg_match_all("/[a-zA-Z]+/", $directoryname, $directoryNameRegex);
    
    // $moviename = preg_replace('/\([0-9]+\)/', '', $directoryname);
    var_dump($directoryNameRegex[0]);
    $dirnameCharsOnly="";
    foreach ($directoryNameRegex[0] as $value) 
    {
        $dirnameCharsOnly= $dirnameCharsOnly.$value;
    }
    $dirnameCharsOnly = strtolower($dirnameCharsOnly. generateRandomString());
    print_r($dirnameCharsOnly);

    



    $movienamebydirectory = str_replace( ' ' . $movieyear['0'], '', $directoryname);
    $querymovie = str_replace( ' ', '%20', $movienamebydirectory);
    $querymovie = str_replace( '(', '%28', $querymovie);
    $querymovie = str_replace( ')', '%29', $querymovie);
    $movieyear = str_replace( '(', '', $movieyear['0']);
    $movieyear = str_replace( ')', '', $movieyear);

    print_r("\n");
    print_r($movieyear);
    print_r("\n");
    print_r($moviediv);
    print_r("\n");
    //sleep so secure api limit https://www.themoviedb.org/faq/api
    usleep(250000);
    // echo "https://api.themoviedb.org/3/search/movie?api_key=$tmdbkey&language=de-DE&query=$querymovie&page=1&include_adult=true&year=$movieyear";
    $json = file_get_contents("https://api.themoviedb.org/3/search/movie?api_key=$tmdbkey&language=de-DE&query=$querymovie&page=1&include_adult=true&year=$movieyear");
    $movie = json_decode($json, true);
    $movienamebytmdb = $movie['results']['0']['title'];
    $movieposter = $movie['results']['0']['poster_path'];
    $vote_average = $movie['results']['0']['vote_average'];
    $vote_count = $movie['results']['0']['vote_count'];    
    
    generateMovieRating($dirnameCharsOnly, $vote_average);
    
    
    //Posts all fetched data to cli. 
    // var_dump($movie);
    // Create html container
    $container= "<div class=\"section\" id=\"$movienamebytmdb\">
    <div class=\"container\">
    <div class=\"row\">
    <div class=\"col-sm-2\">
    <img data-src=\"https://image.tmdb.org/t/p/w500$movieposter\" class=\"img-responsive lazyload\">
    </div>
    <div class=\"col-sm-10\">
    <h1><b>$movienamebytmdb</b></h1>
    <table width=\"100%\">
    <tr>
        <td width=\"25%\">" . get_dir_size_in_gb($result) . " GB </td>
        <td width=\"25%\">" . $movie['results']['0']['original_title'] . "</td>
        <td width=\"25%\"><div class =\"moviecontainer\"id=\"$dirnameCharsOnly\"></div></td>
        <td width=\"25%\">" . $movie['results']['0']['original_title'] . "</td>
    </tr>
    </table>
    <xmp>".$movie['results']['0']['overview'].  "</xmp>
    ";
    // add the created container to the index file
    file_put_contents ( 'index.html', $container, FILE_APPEND);
    
    
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
    <script src="https://rawgit.com/kimmobrunfeldt/progressbar.js/1.0.0/dist/progressbar.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"
            integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl"
            crossorigin="anonymous"></script>
            <link href="https://fonts.googleapis.com/css?family=Raleway:400,300,600,800,900" rel="stylesheet" type="text/css">
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
    <script src="necessary_files/javascript.js"></script>
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
    <script src="https://cdn.rawgit.com/tuupola/jquery_lazyload/0a5e0785a90eb41a6411d67a2f2e56d55bbecbd3/lazyload.js"></script>
    <script type="text/javascript" charset="utf-8">
    window.addEventListener("load", function(event) {
            let images = document.querySelectorAll(".lazyload");
        images.forEach(image => {
            let src = image.getAttribute("data-src");
            image.setAttribute("data-src", src + "?" + Math.random());
        });
        lazyload(images);
    });
    </script>
    <script src="necessary_files/javascript.js"></script>
    </body>
    </html>
    ', FILE_APPEND);
}

function generateMovieRating($id, $rating){
    $rating = $rating / 10;
    $id2 = "js" .$id . "js";
    file_put_contents ( "necessary_files/javascript.js", "
    var $id2 = new ProgressBar.Circle($id, {
        color: '#aaa',
        // This has to be the same size as the maximum width to
        // prevent clipping
        strokeWidth: 4,
        trailWidth: 1,
        easing: 'easeInOut',
        duration: 2000,
        text: {
        autoStyleContainer: false
        },
        from: { color: '#ff0000', width: 1 },
        to: { color: '#00ff00', width: 4 },
        // Set default step function for all animate calls
        step: function(state, circle) {
        circle.path.setAttribute('stroke', state.color);
        circle.path.setAttribute('stroke-width', state.width);
    
        var value = Math.round(circle.value() * 100);
        if (value === 0) {
            circle.setText('');
        } else {
            circle.setText(value);
        }
        }
  });
  $id2.text.style.fontFamily = '\"Raleway\", Helvetica, sans-serif';
  $id2.text.style.fontSize = '2rem';
  
  $id2.animate($rating);  // Number from 0.0 to 1.0
  ", FILE_APPEND);
}

function generateRandomString($length = 10) {
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}
?>