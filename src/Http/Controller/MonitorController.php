<?php

namespace LKDevelopment\UptimeMonitorAPI\Http\Controller;

use Spatie\Url\Url;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Spatie\UptimeMonitor\Models\Monitor;
use Spatie\UptimeMonitor\Exceptions\CannotSaveMonitor;
use Illuminate\Foundation\Validation\ValidatesRequests;

class MonitorController extends Controller
{
    use ValidatesRequests;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Monitor::all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $status = 200;
        $this->validate($request, config('laravel-uptime-monitor-api.validationRules'));
        $url = Url::fromString($request->get('url'));
        try{ 
            Monitor::create([
            'url' => trim($url, '/'),
            'look_for_string' => $request->get('look_for_string') ?? '',
            'uptime_check_method' => $request->has('look_for_string') ? 'get' : 'head',
            'certificate_check_enabled' => $url->getScheme() === 'https',
            'uptime_check_interval_in_minutes' => $request->get('uptime_check_interval_in_minutes'),
            'uptime_check_failure_reason' => ''
            ]);
       } catch (Exception $exception){
            // set status to "conflict" - because we can't create an existing monitor again
            if(get_class($exception) === "Spatie\UptimeMonitor\Exceptions\CannotSaveMonitor"){
                $status = 409;
            } else {
                // set status to bad request because we are unsure why the request failed
                $status = 400;
            }
            return response()->json(['created' => false, 'error' => $exception->getMessage()], $status );
        }
        return response()->json(['created' => true], $status);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return Monitor::findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, config('laravel-uptime-monitor-api.validationRules'));

        $monitor = Monitor::findOrFail($id);
        $url = Url::fromString($request->get('url'));
        $look_for_string = ($request->has('look_for_string')) ? $request->get('look_for_string') : $monitor->look_for_string;
        $monitor->update([
            'url' => $request->get('url'),
            'look_for_string' => $look_for_string,
            'uptime_check_method' => $request->has('look_for_string') ? 'get' : 'head',
            'certificate_check_enabled' => $url->getScheme() === 'https',
            'uptime_check_interval_in_minutes' => $request->get('uptime_check_interval_in_minutes'),
            'uptime_check_failure_reason' => ''
        ]);

        return response()->json(['updated' => true]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $monitor = Monitor::findOrFail($id);
        $monitor->delete();

        return response()->json(['deleted' => true]);
    }
}
