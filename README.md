# local_coursewizard – Course wizard

Moodle plugin by cometas (Moodle 4.5+, prepared for 5.x). Replaces Moodle's "Add a new course" form with a guided, chat-like dialog: the wizard asks for the details step by step and finally creates an ordinary Moodle course.

URL: `local/coursewizard/index.php?category=<id>`. Moodle's "Add a new course" form is not redirected yet.

## How it works

The dialog greets the user by first name and asks the required questions first: course name and category. After that, a green box offers to open the course right away or to add more details. Optional steps follow: description, course image and visibility, plus start and end date if enabled (see settings). Each optional step can be skipped with "Later". From the first optional step on, a bar at the bottom of the screen offers "Open course" at any time. If the site requires further fields (e.g. custom course fields), an extra step asks for them. Fields that are not filled in keep the site defaults.

Every question has a "What is this for?" button that explains where the information is used, e.g. where the course name appears or which aspect ratio suits the course image.

The short name is never asked: it is derived from the course name, and if it is taken, " 2", " 3" … is appended.

Technically, Moodle's own course form runs underneath the dialog (`classes/form/course_form.php`), and the dialog only shows the fields of the current step. Validation and saving are the same as in `course/edit.php`.

## Settings

Site administration → Plugins → Local plugins → Course wizard:

- **Ask for course start and end date** (`local_coursewizard/askdates`, default off): only useful for courses with a fixed term, e.g. at universities.

## Capabilities

Like Moodle's form, the wizard requires `moodle/course:create` in the course category.

## Languages

English and German (informal "du"). All texts are language strings in `lang/`.

## Principles

- Builds on Moodle core only (course form and `create_course()`), no dependency on other cometas plugins.
- No database tables of its own, the plugin only creates ordinary courses.
- Possible later: merge into `local_courseadmin`, AI suggestions for single answers.

## Building JavaScript

`amd/src` → `amd/build` with Moodle's Grunt, locally via `moodle-dev/js-build.sh coursewizard eslint:amd rollup`. `amd/build` is committed.
