<?php
/*
    Copyright (C) 2016  Anastasia Kirjanen, Mika Rönkkö, Sami Kairajärvi, Santtu Kolehmainen

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

namespace App\Http\Controllers;

use DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PlotController extends Controller {
		
	// Returns id, name, country and description of all the plots the user has access to.
	public function getAll(Request $request) {
		$plotIds = implode(',',$request->accessRights);
		$results = DB::select("SELECT id, name, country, description FROM plot WHERE id IN ($plotIds)");
		return response()->json($results);

	}
	
	// Returns id, name, description, date_created and plot from all the plots/exercises user has access to.
	public function getAllExercise(Request $request) {
		$plotIds = implode(',',$request->accessRights);
		$results = DB::select("SELECT id, name, description, plot FROM exercise");
		return response()->json($results);
	}

	// Returns all exercises of the user given plot ID.
	public function getPlotExercise($id, Request $request) {
		if(!is_numeric($id)){
			return response('400: Bad Request', 400);
		}
		if(!in_array($id, $request ->accessRights)) {
			return response('401: Unauthorized', 401);
		}
	
		$results = DB::select("SELECT * FROM exercise WHERE plot = :id",['id' => $id]);
		return response()->json($results);
	}

	// Returns all of the data in the exercise table of the user given exercise ID.
	public function getExercise($ex_id, Request $request) {
		if(!is_numeric($ex_id)){
			return response('400: Bad Request', 400);
		}
		


		// Check if the requested ID matches with any plot in the database.
		$exercise_data = DB::select("SELECT * FROM exercise WHERE id = :id",['id' => $ex_id]);
				
		if ($exercise_data===[]) {
			//return response()->json("NULL");
			return response('404: Not Found', 404);
		}

		//Check if user has access to this id. If not, return HTTP reply 401.
		if(!in_array($exercise_data[0]->plot, $request ->accessRights)) {
			return response('401: Unauthorized', 401);
		}
		
		return response()->json($exercise_data);
		
	}
	
	// Returns requested plot data and all the related tables to the user.
	// If user does not have access to the plot, return HTTP 401.
	// If the requested plot does not exist, return "NULL".
	public function getPlot($id, Request $request) {
		if(!is_numeric($id)){
			return response('400: Bad Request', 400);
		}
		//Check if user has access to this id. If not, return HTTP reply 401.
		if(!in_array($id, $request ->accessRights)) {
			return response('401: Unauthorized', 401);
		}

		// Check if the requested ID matches with any plot in the database.
		$plot_data = DB::select("SELECT * FROM plot WHERE id = :id",['id' => $id]);

		// If the plot does not match any plot, return "404 Not Found".
		if ($plot_data===[]) {
			//return response()->json("NULL");
			return response('404: Not Found', 404);
		}

		// If user had access to one or more plots, send all the tables related to the plot table.
		$tree_data = DB::select("SELECT * FROM tree_data WHERE id_plot = :id",['id' => $id]);
		$tree_data_property_local = DB::select("SELECT * FROM tree_data_property_local WHERE id_plot = :id",['id' => $id]);
		$property_local = DB::select("SELECT * FROM property_local");
		$category = DB::select("SELECT * FROM category");
		$species = DB::select("SELECT * FROM species");
		$species_dictionary = DB::select("SELECT * FROM species_dictionary WHERE plot = :id",['id' => $id]);
		$language = DB::select("SELECT * FROM language");
		$tree_competition = DB::select("SELECT * FROM tree_competition WHERE id_plot = :id",['id' => $id]);

		return response()->json(['plot'=>$plot_data, 
		'tree_data'=>$tree_data,
		'tree_competition' => $tree_competition,		
		'tree_data_property_local'=>$tree_data_property_local,
		'property_local' => $property_local,
		'category' => $category,
		'species' => $species,
		'species_dictionary' => $species_dictionary,
		'language' => $language
		]);
	}
	
	// This function receives exercise results from the user and inserts them into the database.
	// Returns OK if insertion was succesful.
	// Returns INSERT FAILED if insertion fails. Full exception can also be sent for debugging purposes. 
	public function postExercise(Request $request) {
		
		// Initialize optional values to -1 to easily see if some field was left empty
		$score_ecological = -1;
		$score_economic = -1;
		$score_silviculture = -1;
		
		// Check every optional field and replace default values with correct data if they were not empty
		if (!empty($request['exercise'][0]['score_ecological'])) {
			$score_ecological = $request['exercise'][0]['score_ecological'];
		}

		if (!empty($request['exercise'][0]['score_economic'])) {
			$score_economic = $request['exercise'][0]['score_economic'];
		}
		
		if (!empty($request['exercise'][0]['score_silviculture'])) {
			$score_silviculture = $request['exercise'][0]['score_silviculture'];
		}
		// Check if every mandatory field is present.
		// If all mandatory fields are found, execute insert command.
		// Else, return HTTP reply 400.
		try {
			$exercise = $request['exercise'][0]['exercise'];
			$student = $request['exercise'][0]['student'];
			$time_start = $request['exercise'][0]['time_start'];
			$time_end = $request['exercise'][0]['time_end'];
			$result = DB::insert("INSERT INTO exercise_result (`exercise`, `student`, `time_start`, `time_end`, `score_ecological`, `score_economic`, `score_silviculture`) VALUES ('$exercise', '$student', '$time_start', '$time_end', '$score_ecological', '$score_economic', '$score_silviculture')");
		}
		catch(\Exception $e) {
			//return $e;
			return response('400: Bad Request', 400);
		}

		if($result) {
			return response('200: OK', 200);
		}

		else {
			return response('502: Bad Gateway', 502);
		}
	}

/*	Old version
	public function getPlotImg($id) {

		if(!is_numeric($id)){
			return response('400: Bad Request', 400);
		}
		//Check if user has access to this id. If not, return HTTP reply 401.
		if(!in_array($id, $request ->accessRights)) {
			return response('401: Unauthorized', 401);
		}

		// Check if the requested ID matches with any plot in the database.
		//$plot_data = DB::select("SELECT * FROM plot WHERE id = :id",['id' => $id]);

		// If the plot does not match any plot, return "NULL".
		//if ($plot_data===[]) {
		//	//return response()->json("NULL");
		//	return response('404: Not Found', 404);
		//}
		try{
		$imgArray = array();
		$dir = "/srv/www/iplus-uef/images/$id";		
    	$indir = scandir($dir);
		$fileextensions = array(".", "jpg", "jpeg","png");
		$replaceextensions = str_replace($fileextensions, "", $indir);

		$i = 0;
		$len = count($indir);
		$len--;
		while($len >= 2) {
			$img = file_get_contents("$dir/$indir[$len]");
			//$fileName = basename("$dir/$indir[$len]");
			$img64 = base64_encode($img);
			$imgArray[$i] = $img64;
			//$imgArray[] = array(strval($i) => $img64);
			$len--;
			$i++;	
		}	
        	return response()->json($imgArray);

    	}catch(\Exception $e){return $e;}	
		}
	*/
	
	public function getPlotImg($id,Request $request) {

		if(!is_numeric($id)){
			return response('400: Bad Request', 400);
		}
		//Check if user has access to this id. If not, return HTTP reply 401.
		if(!in_array($id, $request ->accessRights)) {
			return response('401: Unauthorized', 401);
		}
		
		
		try{
			$imgArray = array();
			
			//Change the path;
			$dir = "/var/www/html/YOURPATH/images/$id";
		
			$indir = scandir($dir);

			if($indir == false){
				return response('404: not Found', 404);
			}


			// Extensions that are allowed
			$extensions = array('jpg' ,'jpeg', 'png', 'gif', 'bmp');
	
			$result = array();

			// directory to scan
			$directory = new \DirectoryIterator($dir);

			// iterate
			foreach ($directory as $fileinfo) {
    			// must be a file
    			if ($fileinfo->isFile()) {
        		// file extension
				$extension = strtolower(pathinfo($fileinfo->getFilename(), PATHINFO_EXTENSION));
        			// check if extension match
        			if (in_array($extension, $extensions)) {
						$file = $fileinfo->getFilename();
						$img = file_get_contents("$dir/$file");
						$img64 = base64_encode($img);
						//$result[] = $img64;
						// add to result
						//$result[] = $fileinfo->getFilename();
						$result[] = array("ImageName" => $fileinfo->getFilename(), "ImageData" => $img64);
       				}
   				}
			}
			return $result;
			//return response()->json($result);

		}
		catch(\Exception $e){
			//return $e;
			return response('404: not Found', 404);
		
		}

    }
}
?>
