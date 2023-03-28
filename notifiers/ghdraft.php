<?php
// GitHub CLI notifier for XCron
// Creates GH draft issues when triggered
// rob@wisnerd.com

	class XCronNotifier_ghdraft {
		public function __construct() {
			$rootpath = str_replace("\\", "/", dirname(__FILE__));
		}

		public function CheckValid(&$notifyinfo) {
			if (!isset($notifyinfo["project"]))  return array("success" => false, "error" => "Missing project node id.", "errorcode" => "missing_project");
			if (!isset($notifyinfo["code_format"]))  return array("success" => false, "error" => "Missing code_format bool. Set to true if you want your output wrapped in pre tags.", "errorcode" => "missing_code_format");
      if (!isset($notifyinfo["token"])) return array("success" => false, "error" => "Missing GitHub token", "errorcode" => "missing_github_token");
			return array("success" => true);
		}

		public function Notify($notifykey, &$notifyinfo, $numerrors, &$sinfo, $schedulekey, $name, $userdisp, $data) {
			$body = "";
			
			// append the schedule name so it's the first thing we see in notifications
			//$body .= "**Schedule:** " . $name . "\n\n";

			// append a template if there is one
			$template = "/var/scripts/xcron/templates/" . $userdisp . "--" . $name . ".md";
			if (file_exists($template)) {
				$body .= file_get_contents($template) . "\n\n";
			}

			// append the log file if it exists
			$log = "/var/log/xcron/" . $userdisp . "--" . $name . ".log";
			if (file_exists($log)) {
				if ($notifyinfo["code_format"]) {
					$body .= "```\n" . file_get_contents($log) . "```\n";	
				} else {
					$body .= file_get_contents($log);	
				}
			}

			// append the json output if it exists
			if (isset($data["success"])) {
				$body .= json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n";
			}

			$project = $notifyinfo["project"];
			$title = (isset($notifyinfo["prefix"]) ? $notifyinfo["prefix"] : "") . $name;

			// escape quotes in the body
			$body = str_replace('"', '\"', $body);

			$cmd = "/usr/bin/gh api graphql -f query='mutation {addProjectV2DraftIssue(input: {projectId: \"$project\" title: \"$title\" body: \"$body\"}) {projectItem {id}}}'";

			putenv("GH_TOKEN=" . $notifyinfo["token"]);
			$output = shell_exec($cmd);
			
			return array("success" => true);
		}
	}
?>
