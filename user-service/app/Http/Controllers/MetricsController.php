<?php
namespace App\Http\Controllers;
 
use Illuminate\Support\Facades\DB;
 
class MetricsController extends Controller
{
    public function index()
    {
        $totalUsers  = DB::table('users')->count();
        $adminCount  = DB::table('users')->where('role', 'admin')->count();
        $memUsage    = memory_get_usage(true);
 
        $metrics = "# HELP user_service_users_total Total number of users\n";
        $metrics .= "# TYPE user_service_users_total gauge\n";
        $metrics .= "user_service_users_total {$totalUsers}\n\n";
 
        $metrics .= "# HELP user_service_admins_total Total number of admins\n";
        $metrics .= "# TYPE user_service_admins_total gauge\n";
        $metrics .= "user_service_admins_total {$adminCount}\n\n";
 
        $metrics .= "# HELP user_service_memory_bytes Memory usage in bytes\n";
        $metrics .= "# TYPE user_service_memory_bytes gauge\n";
        $metrics .= "user_service_memory_bytes {$memUsage}\n";
 
        return response($metrics, 200)->header('Content-Type', 'text/plain; version=0.0.4');
    }
}