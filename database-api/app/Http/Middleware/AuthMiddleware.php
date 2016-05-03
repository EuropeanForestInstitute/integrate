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

namespace App\Http\Middleware;

use Closure;
use DB;
class AuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
	$authPass = base64_decode($request->header('Authorization'));
	
	//Get plotIDs for given password and store them in array
	$plotIds = DB::select("SELECT plot_id FROM access_rights WHERE password = :pass",['pass' => $authPass]);
	$plotIdArray = [];
	foreach($plotIds as $plotId){
		$plotIdArray[] = intval($plotId->plot_id);
	}
	//No plotIDs for given password, no need to proceed further into application
	if (count($plotIdArray) === 0) {
		return response('401: Unauthorized', 401);  
        }
	$request->accessRights = $plotIdArray; //Add plotIds to request so we can pass them forward to PlotController
    return $next($request);


    }
}
