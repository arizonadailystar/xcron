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

			return array("success" => true);
		}

		public function Notify($notifykey, &$notifyinfo, $numerrors, &$sinfo, $schedulekey, $name, $userdisp, $data) {
			$body = "";
			
			// append a template if there is one
			$template = "/var/scripts/xcron/templates/" . $userdisp . "--" . $name . ".md";
			if (file_exists($template)) {
				$body .= file_get_contents($template) . "\n\n";
			}

			// append the json output if it exists otherwise append the log file if it exists
			if (isset($data["success"])) {
				$jsonData = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
				$body .= str_replace('"', '\"', $jsonData);
			} else {
				$log = "/var/log/xcron/" . $userdisp . "--" . $name . ".log";
				if (file_exists($log)) {
					if ($notifyinfo["code_format"]) {
						$body .= "```\n" . file_get_contents($log) . "```\n";	
					} else {
						$body .= file_get_contents($log);	
					}
				}
			}

			$project = $notifyinfo["project"];
			$title = (isset($notifyinfo["prefix"]) ? $notifyinfo["prefix"] : "") . $name;

			$cmd = "/usr/bin/gh api graphql -f query='mutation {addProjectV2DraftIssue(input: {projectId: \"$project\" title: \"$title\" body: \"$body\"}) {projectItem {id}}}'";

			putenv("GH_TOKEN=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");
			$output = shell_exec($cmd);
			
			return array("success" => true);
		}
	}
?>
