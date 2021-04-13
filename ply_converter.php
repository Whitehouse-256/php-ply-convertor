<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if($argc < 2){
  die("Usage: php $argv[0] [filename.ply]\n");
}

$input = file_get_contents($argv[1]);
$input = str_replace("\r", "", $input);

$vertexNum = 0;
$vertexes = array();
$iv = 0;
$triangleNum = 0;
$triangles = array();
$it = 0;

$scale = 250;

$inHeader = true;

$lines = explode(PHP_EOL, $input);

foreach($lines as $ln => $line){
  $words = explode(" ", $line);
  //echo($ln.". ".$inHeader." ".sizeof($words)." ".$words[0].PHP_EOL);
  if(strpos($words[0], "end_header") !== false) echo("--header end--".PHP_EOL);
  if($inHeader){
    if(sizeof($words) > 2){
      if($words[0] == "element" && $words[1] == "vertex") $vertexNum = (int)$words[2];
      if($words[0] == "element" && $words[1] == "face") $triangleNum = (int)$words[2];
    }
    if(sizeof($words) > 0){
      if(strpos($words[0], "end_header") !== false) $inHeader = false; //leave header
    }
  }else{ //not in header
    if($iv < $vertexNum){ //now reading vertexes
      $vertexes[] = array($scale*(float)$words[0], $scale*(float)$words[1], $scale*(float)$words[2], $scale*(float)$words[3], $scale*(float)$words[4], $scale*(float)$words[5]);
      $iv++;
    }else if($it < $triangleNum){
      $triangles[] = array((int)$words[1], (int)$words[2], (int)$words[3]);
      $it++;
    }
  }
}

$u = array(0, 0.2, 1);
$u_abs = sqrt($u[0]*$u[0] + $u[1]*$u[1] + $u[2]*$u[2]);

$output = "";
foreach($triangles as $t){
  $vertsInTriangle = "";
  $vertsInTriangle .= (int)$vertexes[$t[0]][0].",".(int)$vertexes[$t[0]][1].",".(int)$vertexes[$t[0]][2].";"; //T1
  $vertsInTriangle .= (int)$vertexes[$t[1]][0].",".(int)$vertexes[$t[1]][1].",".(int)$vertexes[$t[1]][2].";"; //T2
  $vertsInTriangle .= (int)$vertexes[$t[2]][0].",".(int)$vertexes[$t[2]][1].",".(int)$vertexes[$t[2]][2].";"; //T3
  //two vectors - sides of the triangle
  $s1 = array((int)$vertexes[$t[1]][0] - (int)$vertexes[$t[0]][0], (int)$vertexes[$t[1]][1] - (int)$vertexes[$t[0]][1], (int)$vertexes[$t[1]][2] - (int)$vertexes[$t[0]][2]);
  $s2 = array((int)$vertexes[$t[2]][0] - (int)$vertexes[$t[0]][0], (int)$vertexes[$t[2]][1] - (int)$vertexes[$t[0]][1], (int)$vertexes[$t[2]][2] - (int)$vertexes[$t[0]][2]);
  //normal vector by cross product
  $n = array($s1[1]*$s2[2]-$s2[1]*$s1[2], $s1[2]*$s2[0]-$s2[2]*$s1[0], $s1[0]*$s2[1]-$s2[0]*$s1[1]);
  $n_abs = sqrt($n[0]*$n[0] + $n[1]*$n[1] + $n[2]*$n[2]);
  //angle from Z axis - for simple coloring
  //$alpha = acos(($u[0]*$n[0]+$u[1]*$n[1]+$u[2]*$n[2])/($u_abs*$n_abs));
  $cos = (($u[0]*$n[0]+$u[1]*$n[1]+$u[2]*$n[2])/($u_abs*$n_abs));
  $am = ($cos/5.0)+0.9;
  $color = array(54, 170, 252);
  $vertsInTriangle .= ((int)min($am*$color[0], 255)).",".((int)min($am*$color[1], 255)).",".((int)min($am*$color[2], 255));


  //here are some sample colors used in recent projects
  
  /** Colors used on apple model (hi-res, final)
  $cos = (($u[0]*$n[0]+$u[1]*$n[1]+$u[2]*$n[2])/($u_abs*$n_abs));
  $am = ($cos/5.0)+0.9;
  $color = array(218, 23, 41);
  $vertsInTriangle .= ((int)($am*$color[0])).",".((int)($am*$color[1])).",".((int)($am*$color[2]));
  apple */

  /*$not_color = array(255-$color[0], 255-$color[1], 255-$color[2]);
  for($i=0; $i<3; $i++){
    $ampl = sin($alpha);
    if($alpha > 3.14159/2) $ampl = 1;
    $not_color[$i] = ($ampl)*$not_color[$i];
  }
  $vertsInTriangle .= ((int)(230-$not_color[0])).", ".((int)(230-$not_color[1])).", ".((int)(230-$not_color[2]))."));";*/
  //$vertsInTriangle .= $vertexes[$t[0]][3].";".$vertexes[$t[0]][4].";".$vertexes[$t[0]][5];
  $output .= $vertsInTriangle.PHP_EOL;
  //echo(strlen($output).PHP_EOL);
}

file_put_contents("out_dotmodel.txt", $output);
