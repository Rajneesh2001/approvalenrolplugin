# Email Approval Verify (enrol_approvalenrol)
A Moodle enrolment plugin that allows users to request course enrolment,
which is approved by the approver.

## Features

- Users can request enrolment and get approved by the approver
- Site admin can assign a specific approver per course
- Approver can approve or reject enrolment requests
- Notifications are sent to the assigned approver, or to the site admin if none is assigned
- Approver dashboard with visual analytics powered by Chart.js

## Requirements
- Moodle 4.4 (tested on 4.4)

## Installation
Install via Git. After installing, log in as an administrator and visit Site administration → Notifications to complete the installation.

### Install with Git
1. Go to your Moodle enrol/directory.
```bash
cd /path/to/moodle/enrol
```
2. Clone the plugin into a folder called enrolapprovalenrol:
```bash
git clone https://github.com/Rajneesh2001/approvalenrolplugin.git
cd approvalenrol
```
3. To update later:
```bash
git pull
```
## License

This plugin is licensed under the [GNU GPL v3](https://www.gnu.org/licenses/gpl-3.0.en.html).


