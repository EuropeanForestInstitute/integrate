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

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

//$app->get('/', function () use ($app) {
//    return $app->version();
//});


/*
Returns description (id, name, description, country) of all available (user has access) plots.
*/
$app->get('plot', 'PlotController@getAll');

/*
Returns all data of the plot, all trees in the plot and all properties of the trees.
*/
$app->get('plot/{id}', 'PlotController@getPlot');

/*
Posts exercise results send by the user to the database.
*/
$app->post('exercise_result', 'PlotController@postExercise');

/*
Returns all images related to the given plot ID.
*/
$app->get('plotimg/{id}', 'PlotController@getPlotImg');

/*
Returns description of all available (user has access) exercises.
*/
$app->get('exercise', 'PlotController@getAllExercise');

/*
Returns all exercises related to the user given plot.
*/
$app->get('plotExercise/{id}', 'PlotController@getPlotExercise');

/*
Returns all data of the given exercise ID.
*/
$app->get('exercise/{ex_id}', 'PlotController@getExercise');


?>