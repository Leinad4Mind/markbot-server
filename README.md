# Travis CI to Canvas proxy

**A small PHP proxy application for adding grades into Canvas after Travis CI checks pass.**

Students will fork assignment repositories from GitHub, make their changes, and create a pull request. The pull request will trigger a series of tests with Travis CI.

After those tests pass, this application is called with specific information to mark their assignment complete inside Canvas.

#### [Check out all the test in the Markbot repo](https://github.com/thomasjbradley/markbot)

---

## Quick setup

It’s a small single page application that expects a query string of parameters. It’s capable of running on Google App Engine, but is not necessary.

☛ `config.example.php` — Rename to just `config.php` and enter your API authentication keys.

```php
$canvas_base_url = 'CANVAS_SUB_DOMAIN'; // example: algonquin.instructure.com
$canvas_api_key = 'CANVAS_API_KEY';
$github_api_key = 'GITHUB_API_KEY';
```

*The GitHub API key is used to get username of the person who submitted the pull request.*

☛ `user-map.example.php` — Rename to just `user-map.php`. Fill with mappings of GitHub usernames to Canvas user IDs.

```php
$user_map = [
  'github-username' => 'canvas-id-number'
];
```

---

## Use

Make a `GET` request to the `grade.php` file (or the `/grade` route if using Google App Engine) with the following query string parameters:

- `gh_repo` — The GitHub repo, in the format of `user/repo`
- `gh_pr` — The pull request ID, a number
- `canvas_course` — The Canvas course ID number
- `canvas_assignment` — The Canvas assignment ID number

**Example request**

```
/grade?gh_repo=acgd-webdev-1%2Ffork-pass-tests&gh_pr=12&canvas_course=123456&canvas_assignment=1234567
```

### Within Travis CI

The above information is available from with Travis:

- `gh_repo` — The `TRAVIS_REPO_SLUG` environment variable
- `gh_pr` — The `TRAVIS_PULL_REQUEST` environment variable

The other information I store in the `.markbot.yml` file of your repository.

- `canvas_course` — The entry in `.markbot.yml` called `canvasCourse`
- `canvas_assignment` — I put this in `.markbot.yml` as `canvasAssignment`

[**Check out Markbot.**](https://github.com/thomasjbradley/markbot)

---

## License & copyright

© 2016 Thomas J Bradley — [MIT License](LICENSE).
