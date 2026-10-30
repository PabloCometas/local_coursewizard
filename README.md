# local_coursewizard – Course wizard

Moodle plugin by cometas (Moodle 4.5+, prepared for 5.x). Replaces Moodle's "Add a new course" form with a guided, chat-like dialog: the wizard asks for the details step by step and finally creates an ordinary Moodle course.

URL: `local/coursewizard/index.php?category=<id>`. Moodle's "Add a new course" form is not redirected yet.

## How it works

The dialog greets the user by first name and asks the required questions first: course name and category. After that, a green box offers to create the course right away or to add more details; once more details are chosen, it disappears. Optional steps follow: description, course image and visibility, plus start and end date if enabled (see settings). Description, image and dates can be skipped with "Later"; visibility is chosen with two buttons ("Show" is preselected) and always needs a decision. Answers can be changed with the pencil; afterwards the dialog returns to where it was. From the first optional step on, a bar at the bottom of the screen offers "Create course" at any time. If the site requires further fields (e.g. custom course fields), an extra step asks for them. Fields that are not filled in keep the site defaults.

Every question has a "What is this for?" button that explains where the information is used, e.g. where the course name appears or which aspect ratio suits the course image.

The short name is never asked: it is derived from the course name, and if it is taken, " 2", " 3" … is appended.

Technically, Moodle's own course form runs underneath the dialog (`classes/form/course_form.php`), and the dialog only shows the fields of the current step. Validation and saving are the same as in `course/edit.php`.

## Course templates (optional)

If [local_coursetemplatewizard](https://github.com/oncampus/moodle-local_coursetemplatewizard) by oncampus is installed and its template category contains courses, a small switch "Create from a template" appears below the greeting (off by default, i.e. an empty course). Turned on, the templates are shown as cards with their course image and a "View" link, the first one is selected. Choosing a template prefills the description step with the description of the template, which can be changed. When the course is opened, it is created as usual and then filled with the chosen template by local_coursetemplatewizard: contents and course format come from the template; name, category, visibility and enrolments are kept. The course name of the template is suggested as course name (selected, so typing replaces it). The image step shows the image of the template with a "Use it" button. Without an own description or image, those of the template are used.

The question is only shown to users who may use templates (`local/coursetemplatewizard:use` in the category, or for the role a creator gets in the new course). Without that plugin, the dialog works as before.

## Event courses (optional)

If mod_facetoface (cometas-events) is installed and the guest enrolment method is enabled (without required password), the dialog first asks "What would you like to create?" with the cards "Course" and "Event". An event course is a course that only offers a session to sign up for, as block_course_explorer lists it under "Events": guest access without password and a visible face-to-face activity.

For an event, the dialog asks for name, category and the date of the first session (start and end, required), optionally places (default 10), description and image; visibility is always "show" and there are no templates. On creation it sets the course format "single activity" (the course opens the face-to-face activity), enables guest access without password, adds the face-to-face activity with the defaults of its form (description = course description) and the first session. Further sessions are added later in the activity.

The choice is only offered to users who may configure guest access and add face-to-face activities and sessions (in the category or with the role a creator gets in the new course).

## Settings

Site administration → Plugins → Local plugins → Course wizard:

- **Ask for course start and end date** (`local_coursewizard/askdates`, default off): only useful for courses with a fixed term, e.g. at universities.

## Capabilities

Like Moodle's form, the wizard requires `moodle/course:create` in the course category.

## Languages

English and German (informal "du"). All texts are language strings in `lang/`.

## Principles

- Builds on Moodle core only (course form and `create_course()`), no dependency on other cometas plugins. local_coursetemplatewizard is used only if installed.
- No database tables of its own, the plugin only creates ordinary courses.
- Possible later: merge into `local_courseadmin`, AI suggestions for single answers.

## Building JavaScript

`amd/src` → `amd/build` with Moodle's Grunt, locally via `moodle-dev/js-build.sh coursewizard eslint:amd rollup`. `amd/build` is committed.
