<?php
require_once("_basic_tasks.php");
$url_this_page = "produce.php";
if(isset($_GET['title'])) $this_title = urldecode($_GET['title']);
else $this_title = '';
require_once("_header.php");
echo "<script type='text/javascript' src='https://www.midijs.net/lib/midi.js'></script>";
set_time_limit(0);

$application_path = $bp_application_path;
if(!file_exists($bp_application_path.$console)) {
	echo "<p style=\"text-align:center; width:90%;\">The console application (file “bp”) is not working, or missing, or misplaced…</p>";
	$source = $application_path."source";
	if(file_exists($source))
		echo "<p style=\"text-align:center; width:90%;\">The source files of BP3 have been found. You can recompile “bp”, then try again.<br /><br /><big>👉&nbsp;&nbsp;<a href=\"".$application_path."php/compile.php?return=produce.php\">Run the compiler</a></big></p>";
	else
		echo "<p style=\"text-align:center; width:90%;\">Source files (the “source” folder) have not been found.<br />Visit <a target=\"_blank\" href=\"https://bolprocessor.org/check-bp3/#install\">https://bolprocessor.org/check-bp3/</a> and follow instructions!</p>";
	die();
	}
$bad_image = FALSE;

if(isset($_POST['ignore'])) {
	if(isset($_POST['running_trace'])) {
		$running_trace = $_POST['running_trace'];
		if(file_exists($running_trace)) {
			@unlink($running_trace);
			echo "<p>Now you can run this project.</p>";
			}
		}
	echo "<p style=\"text-align:center; width:90%;\"><big>👉&nbsp;&nbsp;<a href=\"\" onclick=\"window.close();\">Close this page</a></big></p><p>You can also click PANIC to stop concurrent processes…</p>";
	echo "</div>";
	die();
	}

if(isset($_GET['startup'])) $startup = $_GET['startup'];
else $startup = '';

if(isset($_GET['instruction'])) $instruction = $_GET['instruction'];
else $instruction = '';
if($instruction == '') {
	echo "ERROR: No instruction has been sent";
	echo "<p style=\"text-align:center; width:90%;\"><big>👉&nbsp;&nbsp;<a href=\"\" onclick=\"window.close();\">Close this page</a></big></p>";
echo "</div>";
	die();
	}

if(isset($_GET['here'])) $here = urldecode($_GET['here']);
else $here = '???';
if(isset($_GET['csound_file'])) $csound_file = $_GET['csound_file'];
else $csound_file = '';
if(isset($_GET['tonality_file'])) $tonality_file = $_GET['tonality_file'];
else $tonality_file = '';
if(isset($_GET['item'])) $item = $_GET['item'];
else $item = 0;

$check_command_line = FALSE;
$sound_file_link = $result_file = $tracefile = '';
$project_fullname = '';
// echo "absolute_path = ".$absolute_application_path."<br />";
$temp_dir_abs = $temp_dir;
if(windows_system()) $temp_dir_abs = str_replace("..".SLASH,$absolute_application_path,$temp_dir);
// echo "temp_dir = ".$temp_dir."<br />";
if($instruction == "help") {
	$command = $application_path.$console." --help";
	}
else {
	if(isset($_GET['grammar'])) $project_fullname = $grammar_path = urldecode($_GET['grammar']);
	else $grammar_path = '';
	if(isset($_GET['data'])) $project_fullname = $data_path = urldecode($_GET['data']);
	else $data_path = '';
	if($grammar_path == '' AND $data_path == '') {
		echo "Link to data and/or grammar is missing";
		echo "<p style=\"text-align:center; width:90%;\"><big>👉&nbsp;&nbsp;<a href=\"\" onclick=\"window.close();\">Close this page</a></big></p>";
echo "</div>";
		die();
		}
	if($instruction == "create_grammar") {
		$create_grammar = create_grammar($data_path);
		echo $create_grammar;
		die();
		}
	if(isset($_GET['settings'])) $settings_path = urldecode($_GET['settings']);
	else $settings_path = '';
	if(isset($_GET['objects'])) $objects_path = $_GET['objects'];
	else $objects_path = '';
	if(isset($_GET['note_convention'])) $note_convention = $_GET['note_convention'];
	else $note_convention = '';
	if(isset($_GET['alphabet'])) $alphabet_path = urldecode($_GET['alphabet']);
	else $alphabet_path = '';
	if(isset($_GET['format'])) $file_format = $_GET['format'];
	else $file_format = '';
	if($file_format <> '' AND isset($_GET['output'])) $output = urldecode($_GET['output']);
	else $output = '';
	if(isset($_GET['show_production'])) $show_production = TRUE;
	else $show_production = FALSE;
	if(isset($_GET['trace_production'])) $trace_production = TRUE;
	else $trace_production = FALSE;
	
	$new_random_seed =  FALSE;
	if(isset($_GET['random_seed'])) {
		$random_seed = $_GET['random_seed'];
		$new_random_seed = TRUE;
		}
	if(isset($_GET['test'])) $check_command_line = TRUE;

	if($instruction == "produce" OR $instruction == "produce-all" OR $instruction == "play" OR $instruction == "play-all" OR $instruction == "expand") {
		echo "<script>";
		echo "var sometext = \"<span id=warning><i>Process might take more than ".$max_sleep_time_after_bp_command." seconds.<br />To reduce computation time, you can increase Quantization in the settings</span>\";";
		echo "document.body.innerHTML = sometext";
		echo "</script>";
		}
	$grammar_name = '';
	$data_name = '';
	if($grammar_path <> '') {
		$table = explode(SLASH,$grammar_path);
		$grammar_name = $table[count($table) - 1];
		$dir = str_replace($grammar_name,'',$grammar_path);
		}
		
	if($data_path <> '') {
		$table = explode(SLASH,$data_path);
		$data_name = $table[count($table) - 1];	
		$dir = str_replace($data_name,'',$here);
		}
	
	if(isset($_GET['csound_orchestra'])) {
		$csound_orchestra = $_GET['csound_orchestra'];
		if($csound_orchestra <> '' AND file_exists($dir.$csound_orchestra)) {
			rename($dir.$csound_orchestra,$dir_csound_resources.$csound_orchestra);
			sleep(1);
			}
		}
	else $csound_orchestra = '';
	
	$grammar_name = str_replace(" ","_",$grammar_name);
	$data_name = str_replace(" ","_",$data_name);
	
	// echo "output = ".$output."<br />";
	$project_name = preg_replace("/\.[a-z]+$/u",'',$output);
	// echo "project_name = ".$project_name."<br />";
	if($instruction == "play" OR $instruction == "play-all")
		$result_file = $project_name."_".$instruction."-result.html";
	else $result_file = $project_name."-result.html";
    $result_file = str_replace(SLASH,'/',$result_file);
	// echo "project_name = ".$project_name."<br />";
	// echo "result_file = ".$result_file."<br />";
	$project_fullname = str_replace($temp_dir,'',$project_fullname);
    $project_fullname = str_replace(SLASH,'/',$project_fullname);
	$project_fullname = preg_replace("/\/[0-9]+\.bpda/u",'',$project_fullname);
	$project_fullname = preg_replace("/\/[0-9]+-chunk\.bpda/u",'',$project_fullname);
	$project_fullname = str_replace("_".my_session_id()."_temp",'',$project_fullname);
	$table = explode('/',$project_fullname);
	$project_fullname = end($table);
    // echo "project_fullname = ".$project_fullname."<br />";
    $trace_link = $temp_dir."trace_".my_session_id()."_".$project_fullname.".txt";
	$tracefile = $temp_dir."trace_".my_session_id()."_".$project_fullname.".txt";
    $trace_link = str_replace(SLASH,'/',$trace_link);
 //   $tracefile = str_replace(SLASH,'/',$tracefile);
//	echo "<p>Trace file = ".$tracefile."</p>";

	$midiport = str_replace(".txt","_midiport",$tracefile);
//	echo "<p>midiport file = ".$midiport."</p>";
	if($file_format == "rtmidi" AND !file_exists($midiport)) {
		echo "<p style=\"text-align:center;\"><big>You first need to save the project or MIDI ports</big></p>";
		echo "<p style=\"text-align:center; width:90%;\"><big>👉&nbsp;&nbsp;<a href=\"\" onclick=\"window.close();\">Close this page</a></big></p>";
		echo "</div>";
		die();
		}

	$midifile = $project_name.".mid";
	if(file_exists($midifile)) {
	//	echo "<p>midifile = ".$midifile."</p>";
		@unlink($midifile);
		}

	$htmlfile = $project_name.".html";
	if(file_exists($htmlfile)) {
	//	echo "<p>htmlfile = ".$htmlfile."</p>";
		@unlink($htmlfile);
		}

	$time_start = time();
	$time_end = $time_start + 3;
/*	while(TRUE) {
		if(!file_exists($output) AND !file_exists($tracefile) AND !file_exists($result_file)) break;
		if(time() > $time_end) break;
		sleep(1);
		} */
	@unlink($result_file);
	if($output <> '') @unlink($output);
	@unlink($trace_csound);
	$trace_csound = '';
	
	/* echo "application_path = ".$application_path."<br />";
	echo "console = ".$console."<br />"; */
	$command = $application_path.$console." ".$instruction;
	
	if($grammar_path <> '') {
		$thisgrammar = $grammar_path;
		if(is_integer(strpos($thisgrammar,' ')))
			$thisgrammar = '"'.$thisgrammar.'"';
		}
	
	if($data_path <> '') {
		$thisdata = $data_path;
		if(is_integer(strpos($thisdata,' ')))
			$thisdata = '"'.$thisdata.'"';
		}
	
	if($settings_path <> '') {
		$thissettings = $settings_path;
		if(is_integer(strpos($thissettings,' ')))
			$thissettings = '"'.$thissettings.'"';
		}

	$thisalphabet = $alphabet_path;
	if(is_integer(strpos($thisalphabet,' ')))
		$thisalphabet = '"'.$thisalphabet.'"';
		
	$thisobject = $objects_path;
	if(is_integer(strpos($thisobject,' ')))
		$thisobject = '"'.$thisobject.'"';
	
	if(is_integer(strpos($output,' ')))
		$output = '"'.$output.'"';
		
	if($settings_path <> '') $command .= " -se ".$thissettings;
	if($data_path <> '') $command .= " -da ".$thisdata;
	if($grammar_path <> '') $command .= " -gr ".$thisgrammar;
	if($alphabet_path <> '') $command .= " -al ".$thisalphabet;
	if($objects_path <> '') $command .= " -so ".$thisobject;

	if($note_convention <> '') $command .= " --".$note_convention;
	if($csound_file <> '') $command .= " -cs ".$dir_csound_resources.$csound_file;
	if($tonality_file <> '') $command .= " -to ".$dir_tonality_resources.$tonality_file;
	
	if($startup <> '') $command .= " --start ".$startup;
	if($instruction == "produce" OR $instruction == "produce-all" OR $instruction == "play" OR $instruction == "play-all" OR $instruction == "expand") {
		switch($file_format) {
			case "data":
			//	$command .= " -d -o ".$output;
				$command .= " -o ".$output;
				break;
			case "midi":
			//	$command .= " -d --midiout ".$output;
				$command .= " --midiout ".$output;
				break;
			case "csound":
			//	$command .= " -d --csoundout ".$output;
				$command .= " --csoundout ".$output;
				break;
			default:
				$command .= " --rtmidi"; // We use the default destination
		//		$command .= " -d --rtmidi"; // We use the default destination
				break;
			}
		}
	if($tracefile <> '') $command .= " --traceout ".$tracefile;
	if($show_production) $command .= " --show-production";
	if($trace_production) $command .= " --trace-production";
	if($new_random_seed) $command .= " --seed ".$random_seed;
	}

if($instruction <> "help") {
	// Check that the same project is not already running in the same sesssion
	$running_trace = $temp_dir_abs."trace_".my_session_id()."_".$project_fullname.".txt";
	// Example: trace_dnh20dhdkjj9nd1ehr9kj360ec_-gr.Mozart.txt
	if(file_exists($running_trace)) {
		echo "<form  id=\"topchanges\" method=\"post\" action=\"".$url_this_page."\" enctype=\"multipart/form-data\">";
		echo "<p style=\"color:red;\">Project “".$project_fullname."” is already running in the same session.</p>";
		// echo $running_trace."<br />";
		echo "<input type=\"hidden\" name=\"running_trace\" value=\"".$running_trace."\">";
		echo "<input style=\"color:DarkBlue; background-color:yellow;\" onclick=\"window.close();\" type=\"submit\" value=\"WAIT UNTIL IT IS OVER\">";
		echo "&nbsp;&nbsp;<input style=\"background-color:Aquamarine;\" type=\"submit\" name=\"ignore\" value=\"IGNORE\">";
		echo "</form>";
		die();
		}
	// Delete old images
	$dircontent = scandir($temp_dir_abs);
	foreach($dircontent as $thisfile) {
		$time_saved = filemtime($temp_dir_abs.$thisfile);
	//	echo $thisfile." ➡ ".date('Y-m-d H\hi',$time_saved)."<br />";
		$table = explode('_',$thisfile);
		if($table[0] <> "trace") continue;
		if(!isset($table[2]) OR $table[1] <> my_session_id()) continue;
		$found = FALSE; $this_name = '';
		for($i = 2; $i < (count($table) - 1); $i++) {
			if($table[$i] == "image" AND is_integer(strpos($thisfile,$project_fullname))) {
				$this_name = $table[$i - 1];
				$found = TRUE;
				break;
				}
			}
		if(!$found) continue;
		// Delete this image to be replaced with the current one
		$rep = @unlink($temp_dir_abs.$thisfile);
//		echo "deleted<br />"; 
		// Make sure deletion is complete before launching the command
		$time_start = time();
		$time_end = $time_start + 5;
		if($rep) while(TRUE) {
			if(!file_exists($temp_dir_abs.$thisfile)) break;
			if(time() > $time_end) break;
			sleep(1);
			}
		}
	}

if($check_command_line) {
	echo "<p><i>Run this command in the “php” folder:</i></p>";
	echo "<p><font color=\"red\">➡</font> ".$command."</p>";
	die();
	}
$command_show = $command;
if(windows_system()) {
    $command_show = $command = windows_command($command);
    $command_show = str_replace('^','',$command_show);
    }
echo "<p><small>command = <font color=\"red\">".$command_show."</font></small></p>\n";

$stopfile = $temp_dir_abs."trace_".my_session_id()."_".$project_fullname."_stop";
// This will be used by createFile() after clicking the STOP button in produce.php

$donefile = $temp_dir_abs."trace_".my_session_id()."_".$project_fullname."_done";
// This is created by the console to tell its job is over

if($instruction <> "help") {
	if($tracefile <> '') @unlink($tracefile);
	if(file_exists($dir_midi_resources."midiport_".$project_fullname))
		// We need to use midisetup of this project
		copy($dir_midi_resources."midiport_".$project_fullname, $dir_midi_resources."last_midiport");
	if($csound_file <> '') {
		$lock_file = $dir_csound_resources.$csound_file."_lock";
	//	echo "Csound instruments lock_file = ".$lock_file."<br />";
		$time_start = time();
		$time_end = $time_start + 5;
		while(TRUE) {
			if(!file_exists($lock_file)) break;
			if(time() > $time_end) {
				echo "<p id=\"cswait\"><font color=\"red\">Maximum time (5 seconds) spent waiting for the Csound resource file to be unlocked:</font> <font color=\"blue\">".$dir_csound_resources.$csound_file."</font></p>";
				break;
				}
			}
		}
	if($objects_path <> '') {
		$lock_file = $objects_path."_lock";
	//	echo "Sound-object prototypes lock_file = ".$lock_file."<br />";
		$time_start = time();
		$time_end = $time_start + 5;
		while(TRUE) {
			if(!file_exists($lock_file)) break;
			if(time() > $time_end) {
				echo "<p id=\"miwait\"><font color=\"red\">Maximum time (5 seconds) spent waiting for the sound-object prototypes file to be unlocked:</font> <font color=\"blue\">".$objects_path."</font></p>";
				break;
				}
			}
		}
    $stopfile = str_replace(SLASH,'/',$stopfile);
	echo "<p id=\"wait\" style=\"text-align:center; background-color:yellow;\"><br /><big><b><span class=\"blinking\">… Bol Processor console is working …</span></b></big><br />(Don't close this window!)<br /><br /><button type=\"button\" class=\"bouton\" onclick=\"createFile('".$stopfile."');\">Click to STOP</button><br /><br /></p>\n";
	}
echo str_repeat(' ', 1024);  // send extra spaces to fill browser buffer, useful for Windows
ob_flush();
flush();

if(isset($data_path) AND $data_path <> '') {
	$content = @file_get_contents($data_path,TRUE);
	if($content <> FALSE) {
		if($instruction == "play") echo "<p><b>Playing";
		if($instruction == "play-all") echo "<p><b>Playing chunks";
		if($instruction == "expand") echo "<p><b>Expanding";
		if($item <> 0) echo " #".$item;
		else echo ":";
		echo "</b></p>";
		echo "<p style=\"color:MediumTurquoise;\"><b>";
		$table = explode(chr(10),$content);
		for($i = $k = 0; $i < count($table); $i++) {
			if($k > 800) {
				echo "… … …<br />";
				break;
				}
			$line = trim($table[$i]);
			$line = recode_tags($line);
			$line_show = $line;
			if($line == '') continue;
			$length = strlen($line);
			if($length > 200)
				$line_show = substr($line,0,50)."<br />&nbsp;&nbsp;... ... ...<br />".substr($line,-100,100);
			echo $line_show."<br />";
			$k += strlen($line_show);
			}
		echo "</b></p>";
		}
	}
@unlink($stopfile); @unlink($panicfile);
session_abort();
// $command = "ASAN_OPTIONS=detect_leaks=0 ".$command; // This is for debugging
$o = send_to_console($command);
// if($pid > 0) echo "<small>The pid was <font color=\"red\">".$pid."</font></small><br />";
echo "<hr>";
// sleep(1);
session_reset();
// sleep(1);
$n_messages = count($o);
$no_error = FALSE;
for($i = 0; $i < $n_messages; $i++) {
	$mssg = $o[$i];
	if(is_integer($pos=strpos($mssg,"Errors: 0")) AND $pos == 0) $no_error = TRUE;
	}

if($instruction == "help") {
	for($i=0; $i < $n_messages; $i++) {
		$mssg = $o[$i];
		$mssg = clean_up_encoding(FALSE,TRUE,$mssg);
		echo $mssg."<br />";
		}
	die();
	}

$last_warning = $time_start = time();
$time_end = $time_start + $max_sleep_time_after_bp_command;
//	echo $donefile."<br />";
/*	echo "<p id=\"donestatus\">Waiting for ‘done’ file…</p>";
$dots = 0;
while(TRUE) {
	if(file_exists($donefile)) break;
	if(time() > $time_end) {
		echo "<p><font color=\"red\">Maximum time (".$max_sleep_time_after_bp_command." seconds) spent waiting for the 'done.txt' file… The process is incomplete!</font></p>";
		break;
		}
	sleep(1);
	$time_done = time() - $last_warning;
	if($time_done > 1) {
		if($dots == 0) echo "<br /><br />Waiting ";
		else echo ".";
		$last_warning = time();
		$dots++;
		}
	}
if($dots > 0) echo "<br /><br />"; */
@unlink($donefile);
$content_trace = $tracefile_html = '';
if(file_exists($tracefile)) {
    $content_trace = file_get_contents($tracefile,TRUE);
    $tracefile_html = clean_up_file_to_html($tracefile);
    }
$trace_link = str_replace(".txt",".html",$trace_link);
$output = str_replace(SLASH,'/',$output);
$output_link = $output;
$test = FALSE;
if($test) echo "<br />output = ".$output."<br />";
if($test) echo "tracefile = ".$tracefile."<br />";
if($test) echo "tracefile_html = ".$tracefile_html."<br />";
if($test) echo "dir = ".$dir."<br />";
if($test) echo "trace_link = ".$trace_link."<br />";
if($test) echo "output_link = ".$output_link."<br />";
if($test) echo "file_format = ".$file_format."<br />";
echo "<script>";
echo "document.getElementById('warning').style.display = \"none\";";
echo "document.getElementById('wait').style.display = \"none\";";
echo "document.getElementById('miwait').style.display = \"none\";";
echo "document.getElementById('cswait').style.display = \"none\";";
echo "</script>";

if(!$no_error) {
	if(strlen($content_trace) > 4) {
		echo "<p><font color=\"red\" class=\"blinking\">Errors found… </font> ";
		echo "Check the <a onclick=\"window.open('".$trace_link."','errors','width=800,height=500,left=400'); return false;\" href=\"".$trace_link."\">error trace</a> file!</p>";
		}
	}
echo "<p>";
if($output <> '' AND $file_format <> "midi" AND $file_format <> "rtmidi") {
	if($file_format <> "csound") {
		$output_html = clean_up_file_to_html($output);
		$output_link = $output_html;
		}
	echo "<font color=\"red\">➡</font> Read the <a onclick=\"window.open('".$output_link."','".$grammar_name."','width=800,height=700,left=300'); return false;\" href=\"".$output_link."\">output file</a> (or <a href=\"".$output_link."\" download>download it</a>)<br />";
	}
if($trace_production OR $instruction == "templates" OR $show_production) {
    if(file_exists($trace_link) AND $content_trace > 4) 
        echo "<font color=\"red\">➡</font> Read the <a onclick=\"window.open('".$trace_link."','trace','width=800,height=600,left=400'); return false;\" href=\"".$trace_link."\">trace file</a> (or <a href=\"".$trace_link."\" download>download it</a>)";
    }
echo "</p>";
	
// Show MIDI file
if($file_format == "midi") {
	$midi_file_link = $output;
//	echo "<p>output = ".$output."</p>";
	if(file_exists($midi_file_link) AND filesize($midi_file_link) > 59) {
//		echo "midi_file_link = ".$midi_file_link."<br />";
		if(!is_connected() AND $file_format == "midi") {
			echo "<p style=\"color:red;\">➡ Cannot find the MIDI file player “midijs.net”… Are you connected to Internet?</p>";
			echo "<p><a href=\"".$midi_file_link."\" download>Download the MIDI file</a></p>";
			}
		else {
			$midi_file_link_url = str_replace(SLASH,'/',$midi_file_link);
			echo "<p class=\"shadow\" style=\"width:25em;\"><a href=\"#midi\" onClick=\"MIDIjs.play('".$midi_file_link_url."');\"><img src=\"pict/loudspeaker.png\" width=\"70px;\" style=\"vertical-align:middle;\" />Play MIDI file</a>";
			echo " (<a href=\"#midi\" onClick=\"MIDIjs.stop();\">Stop playing</a>)";
			echo "&nbsp;or <a href=\"".$midi_file_link."\" download>download it</a></p>";
			}
		}
	}

// Prepare images if any
$dircontent = scandir($temp_dir_abs);
echo "<table style=\"background-color:snow; padding:0px;\"><tr>";
$position_image = 0;
// echo "<p>project_fullname = ".$project_fullname."</p>";
foreach($dircontent as $thisfile) {
	$table = explode('_',$thisfile);
	if($table[0] <> "trace") continue;
	if(!isset($table[2]) OR $table[1] <> my_session_id()) continue;
	$found = FALSE; $this_name = '';
//	echo "<p>".$thisfile."</p>";
	for($i = 2; $i < (count($table) - 1); $i++) {
		if($table[$i] == "image" AND is_integer(strpos($thisfile,$project_fullname))) {
			$this_name = $table[$i - 1];
			$found = TRUE;
			break;
			}
		}
	if(!$found) continue;
//	echo "<p>FOUND: ".$thisfile."</p>";
	echo "<td style=\"background-color:white; border-radius: 6px; border: 4px solid Gold; vertical-align:middle; text-align: center; padding:8px; margin:0px;\>";
	$number_and_time = str_replace(".html",'',$table[$i + 1]);
	$table_number = explode('-',$number_and_time);
	$number = $table_number[0];
	$image_time = '';
	if(count($table_number) > 1) $image_time = $table_number[1]."s";
//		if($image_time == "0.00s") $image_time = '';
//		$number = intval($number_and_time);
	$content = @file_get_contents($temp_dir_abs.$thisfile,TRUE);
	$table2 = explode(chr(10),$content);
	$imax = count($table2);
	$table3 = array();
	$title1 = $this_name."_Image_".$number;
	$WidthMax = $HeightMax = 0;
	$position_image++;
	for($i = 0; $i < $imax; $i++) {
		$line = trim($table2[$i]);
	//	echo $i." ".recode_tags($line)."<br />";
		if(is_integer($pos=strpos($line,"canvas.width"))) {
			$table4 = explode("=",$line);
			$WidthMax = round(intval($table4[2])) + 10;
		//	echo $i.") WidthMax = ".$WidthMax."<br />";
			}
		if(is_integer($pos=strpos($line,"canvas.height"))) {
			$table4 = explode("=",$line);
			$HeightMax = round(intval($table4[2]));
		//	echo $i.") HeightMax = ".$HeightMax."<br />";
			}
		}
	for($i = $j = 0; $i < $imax; $i++) {
		$line = trim($table2[$i]);
		$table3[$j] = $line;
		$j++;
		}
	$link = $temp_dir.$thisfile;
    $link = str_replace(SLASH,'/',$link);
	$left = 10 + (50 * ($position_image - 1));
	$window_height = 600;
	if($HeightMax < $window_height) $window_height = $HeightMax + 60;
	$window_width = 1200;
	if($WidthMax < $window_width) $window_width = $WidthMax +  20;
	echo "<div style=\"border:2px solid gray; background-color:azure; width:8em;  padding:2px; text-align:center; border-radius: 6px;\"><a onclick=\"window.open('".$link."','".$title1."','width=".$window_width.",height=".$window_height.",left=".$left."'); return false;\" href=\"".$link."\">Image ".$number."</a><br />".$image_time;
	if(check_image($link) <> '') {
		$bad_image = TRUE;
		echo " <font color=\"red\"><b>*</b></font>";
		}
	echo "</div>&nbsp;";
	echo "</td>";
	if(++$position_image > 11) {
		$position_image = 0;
		echo "</tr><tr>";
		}
	}
echo "</tr></table>";
echo "<br />";
	
// Process Csound score if possible
if($no_error AND $file_format == "csound") {
	if($csound_orchestra == '') {
		$csound_orchestra = "0-default.orc";
		echo "<p><font color=\"red\">➡</font> Csound orchestra file was not specified. I tried the default orchestra: <font color=\"blue\">".$dir_csound_resources.$csound_orchestra."</font>.</p>";
		}
	if(!file_exists($dir_csound_resources.$csound_orchestra)) {
		echo "<p><font color=\"red\">➡</font> No orchestra file has been found here: <font color=\"blue\">".$dir_csound_resources.$csound_orchestra."</font>. Csound will not create a sound file.</p>";
		}
	else {
		$csound_file_link = $output;
       // echo "csound_file_link = ".$csound_file_link."<br />";
		$sound_file_link = str_replace(".sco",'',$csound_file_link);
		// We change the name of the sound file every time to force the browser to refresh the audio tag
       // echo "sound_file_link = ".$sound_file_link."<br />";
        $sound_file_link .= "@".rand(10000,99999).".wav";
      //  echo "sound_file_link = ".$sound_file_link."<br />";
		$table = explode('/',$csound_file_link);
		$csound_file_name = end($table);
       // echo "csound_file_name = ".$csound_file_name."<br />";
		$project_name = str_replace(".sco",'',$csound_file_name);
      //  echo "project_name = ".$project_name."<br />";
		$dir = str_replace($csound_file_name,'',$csound_file_link);
      //  echo "dir = ".$dir."<br />";
		$dircontent = scandir($dir);
		foreach($dircontent as $thisfile) {
			$table = explode('.',$thisfile);
			$extension = end($table);
			if($extension <> "wav") continue;
			$table = explode('@',$table[0]);
			if($table[0] == $project_name) @unlink($dir.$thisfile);
			}
		$time_start = time();
		$time_end = $time_start + 5;
		while(TRUE) {
			if(!file_exists($sound_file_link)) break;
			if(time() > $time_end) break;
			}
		if(file_exists($csound_file_link)) {
            $this_file = "csound_version.txt";
            if(!file_exists($this_file)) {
				echo "<p><font color=\"red\">➡</font> Test of Csound was unsuccessful. May be not installed?</p>";
				}
			else {
				sleep(1);
				$time_start = time();
                if(windows_system())
                    $command = "\"".$programFiles.SLASH.$csound_path.SLASH.$csound_name."\"  --wave -o ".$sound_file_link." ".$dir_csound_resources.$csound_orchestra." ".$csound_file_link;
		        else
                    $command = $csound_path.SLASH.$csound_name." --wave -o ".$sound_file_link." ".$dir_csound_resources.$csound_orchestra." ".$csound_file_link;
                $trace_csound = $temp_dir_abs."trace_csound.txt";
				$command .= " 2>".$trace_csound;
                $command_show = $command;
                if(windows_system()) {
                    $command_show = $command = windows_command($command);
                    $command_show = str_replace('^','',$command_show);
                    }
                echo "<p><small>command = <font color=\"red\">".$command_show."</font></small></p>";
				exec($command,$result_csound,$return_var);
				if($return_var <> 0) {
					echo "<p><font color=\"red\">➡</font> Csound returned error code <font color=\"red\">‘".$return_var."’</font>.<br /><i>Maybe you are trying to use instruments that do not match</i> <font color=\"blue\">‘".$csound_orchestra."’</font></p>";
					}
				else {
					$time_spent = time() - $time_start;
					if($time_spent > 10)
						echo "<p><font color=\"red\">➡</font> Sorry for the long time (".$time_spent." seconds) waiting for Csound to complete the conversion…</p>";
					$audio_tag = "<audio controls class=\"shadow\">";
					$audio_tag .= "<source src=\"".$sound_file_link."\" type=\"audio/wav\">";
					$audio_tag .= "Your browser does not support the audio tag.";
					$audio_tag .= "</audio>";
					echo $audio_tag;
					echo "<p><a target=\"_blank\" href=\"".$sound_file_link."\" download>Download this sound file</a> (<font color=\"blue\">".$sound_file_link."</font>)</p>";
					echo "<p><font color=\"red\">➡</font> If you hear garbage sound or silence it may be due to a mismatch between Csound score and orchestra<br />&nbsp;&nbsp;&nbsp;or some overflow in Csound…</p>";
					}
				}
			}
		else echo "<p><font color=\"red\">➡</font> The score file (".$csound_file_link.") was not found by Csound.</p>";
		}
	}

$handle = FALSE;
$terminated = FALSE;
if(file_exists($stopfile) OR file_exists($panicfile)) $terminated = TRUE;
if($terminated) echo "<p style=\"color:brown;\"><big>👉 The performance has been interrupted.</big></p>";
if($n_messages > 6000) echo "<p><font color=\"red\">➡</font> Too many messages produced! (".$n_messages.")</p>";
else {
	if($result_file <> '') $handle = fopen($result_file,"w");
	if($handle) {
		$header = "<!DOCTYPE HTML>";
		$header .= "<html lang=\"en\">";
		$header .= "<head>\n";
		$header .= "<meta content=\"text/html; charset=utf-8\" http-equiv=\"Content-Type\" />\n";
		$header .= "<link rel=\"stylesheet\" href=\"".$bp_application_path."php/bp.css\" />\n";
		$header .= "<title>".$result_file."</title>\n";
		$header .= "<script type='text/javascript' src='https://www.midijs.net/lib/midi.js'></script>\n";
		$header .= "<script src=\"https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js\"></script>\n";
		$header .= "</head><body>\n";
		fwrite($handle,$header."\n");
		fwrite($handle,"<h2 id=\"midi\">".$grammar_name."</h2>\n");
		fwrite($handle,"<small>Results as per ".date('Y-m-d H:i:s')."</small><br />\n");
		if(file_exists($project_name.".wav")) {
			$audio_tag = "<p><b>Csound:</b></p><audio controls class=\"shadow\">";
			$audio_tag .= "<source src=\"".$project_name.".wav\" type=\"audio/wav\">";
			$audio_tag .= "Your browser does not support the audio tag.";
			$audio_tag .= "</audio>";
			fwrite($handle,$audio_tag."<p>\n");
			if(file_exists($project_name.".sco")) {
				fwrite($handle,"<br /><a target=\"_blank\" href=\"".$project_name.".sco\" download>Download Csound score</a> (<font color=\"blue\">".$project_name.".sco</font>)<br />");
				}
			fwrite($handle,"<a target=\"_blank\" href=\"".$project_name.".wav\" download>Download this sound file</a> (<font color=\"blue\">".$project_name.".wav</font>)</p>");
			}
		if(file_exists($project_name.".mid") AND filesize($project_name.".mid") > 59) {
		//	echo "mid = ".$project_name.".mid<br />";
            $midi_url = str_replace(SLASH,'/',$project_name.".mid");
			$audio_tag = "<p><b>MIDI file:</b></p><p class=\"shadow\" style=\"width:25em;\"><a href=\"#midi\" onClick=\"MIDIjs.play('".$midi_url."');\"><img src=\"".$bp_application_path."php/pict/loudspeaker.png\" width=\"70px;\" style=\"vertical-align:middle;\" />Play MIDI file</a>";
			$audio_tag .= " (<a href=\"#midi\" onClick=\"MIDIjs.stop();\">Stop playing</a>)";
			$audio_tag .= "&nbsp;or <a href=\"".$project_name.".mid\" download>download it</a></p>";
			fwrite($handle,$audio_tag."\n");
			}
		if(file_exists($project_name.".html")) {
			fwrite($handle,"<p><font color=\"red\">➡</font> Read the <a onclick=\"window.open('".$project_name.".html','".$file_format."','width=800,height=700,left=300'); return false;\" href=\"".$project_name.".html\">data file</a> (or <a href=\"".$project_name.".bpda\" download>download it</a>)</p>\n");
			}
		fwrite($handle,"<hr><p><b>Messages:</b></p>\n");
		}
	}
if($n_messages > 0) {
	$warnings = 0;
	for($i=0; $i < $n_messages; $i++) {
		$mssg = $o[$i];
		$mssg = clean_up_encoding(FALSE,TRUE,$mssg);
		$mssg = str_replace('<',"&lt;",$mssg);
		$mssg = str_replace('>',"&gt;",$mssg);
		if(is_integer($pos=strpos($mssg,"=&gt; "))) {
			$warnings++;
		//s	$mssg = preg_replace("/^=&gt;\s/u"," ",$mssg);
			$mssg = str_replace("=&gt; ",'',$mssg);
			$mssg = "<font color=red>".$mssg."</font>";
			}
   //     if(windows_system())
            $mssg = preg_replace("/(C:.+)$/u","<font color=blue><small>$1</small></font>",$mssg);
   //     else
			$mssg = preg_replace("/(\.\.\/.+)$/u","<font color=blue><small>$1</small></font>",$mssg);
		if($mssg == "(null)") continue;
		if($handle) fwrite($handle,$mssg."<br />\n");
		if($i == 7) echo "… … …<br />";
		if($i < 7 OR $i > ($n_messages - 4)) echo $mssg."<br />";
		}
	if($handle) {
		$window_name = $grammar_name."_".rand(0,99)."_result";
		if($bad_image) echo "<p>(<font color=\"red\"><b>*</b></font>) Syntax error in image: negative argument</p>";
		echo "<p style=\"font-size:larger;\"><input style=\"color:DarkBlue; background-color:yellow; font-size:large;\" onclick=\"window.open('".$result_file."','".$window_name."','width=800,height=600,left=100'); return false;\" type=\"submit\" value=\"Show all ".$n_messages." lines\">";
		if($warnings == 1) echo " <span class=\"blinking\">=> ".$warnings." warning</span>";
		if($warnings > 1) echo " <span class=\"blinking\">=> ".$warnings." warnings</span>";
		}
	if($handle) fwrite($handle,"</body>\n");
	if($handle) fclose($handle);
	}
else echo "<p>No message produced… Bug in the console?";
if($trace_csound <> '' AND file_exists($trace_csound)) {
	$window_name = $grammar_name."_".rand(0,99)."_result";
	echo "&nbsp;<input style=\"color:DarkBlue; background-color:yellow; font-size:large;\" onclick=\"window.open('".$trace_csound."','".$window_name."','width=800,height=600,left=100'); return false;\" type=\"submit\" value=\"Show Csound trace\">";
	}
echo "</p>";

@unlink($running_trace);

function check_image($link) {
	$result = '';
	$content = @file_get_contents($link,TRUE);
	if($content) {
		$content = str_replace(chr(13).chr(10),chr(10),$content);
		$content = str_replace(chr(13),chr(10),$content);
		$pos1 = $pos2 = 0;
		if((is_integer($pos1 = strrpos($content,",-")) OR is_integer($pos2 = strrpos($content,"(-"))) AND is_integer($pos3 = strpos($content,"//"))) {
			if($pos1 > $pos3 OR $pos2 > $pos3) $result = "negative argument";
			}
		}
	return $result;
	}

?>
