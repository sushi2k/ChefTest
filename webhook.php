<?php
function cidr_match($ip, $ranges)
{
    $ranges = (array)$ranges;
    foreach($ranges as $range) {
        list($subnet, $mask) = explode('/', $range);
        if((ip2long($ip) & ~((1 << (32 - $mask)) - 1)) == ip2long($subnet)) {
            return true;
        }
    }
    return false;
}

$github_ips = array('207.97.227.253', '50.57.128.197', '108.171.174.178', '50.57.231.61');
$github_cidrs = array('204.232.175.64/27', '192.30.252.0/22');

# define repo names
$repoTools = "tools";  					// this was used for testing purpose, can be deleted

# check if request is comming from GitHub IP range
if(in_array($_SERVER['REMOTE_ADDR'], $github_ips) || cidr_match($_SERVER['REMOTE_ADDR'], $github_cidrs)) {

        #get JSON from parameter payload in POST body (content-type: application/x-www-form-urlencoded)
        $rawData= $_POST["payload"];
        #decode JSON and UTF8 encode it first, os otherwise $data will be null
        $data = json_decode(utf8_encode($rawData));
        # get the repo name of the POST request
        $repo = $data->repository->full_name;

        $dir = '/opt/';

        switch ($repo) {
            case "sushi2k/".$repoTools;
                error_log("Repo found: ".$repo.PHP_EOL, 3, "/tmp/escrow.log");
                $dir = $dir.$repoTools;
                exec("cd $dir && git pull 2>&1", $output);
                break;
	    default:
                error_log("JSON Error: ".json_last_error().PHP_EOL, 3, "/tmp/escrow.log");
                error_log("Repo not found: ".$repo.PHP_EOL, 3, "/tmp/escrow.log");
                error_log("Raw JSON: ".$rawData.PHP_EOL, 3, "/tmp/escrow.log");
        }
}
else {
        header('HTTP/1.1 404 Not Found');
        echo '404 Not Found.';
        exit;
}
