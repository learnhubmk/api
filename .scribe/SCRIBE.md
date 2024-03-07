#### Working with Scribe API Documentation

Access the documentation:

- With Docker:
    - `learnhub.test:8000/docs`
- Without Docker
    - `localhost:8000/docs`

In order to generate documentation for APIs use: `php artisan scribe:generate`

#### Working with Socialite

##### Github

- Go to https://github.com/settings/developers and create new application.
- You need to provide app name, callback url and website name.
- Callback url should be the redirect url to your application for e.g. `localhost/login/github`
- From the dashboard get the `APP ID` and paste into `GITHUB_CLIENT_ID`, and `APP SECRET` into `GITHUB_CLIENT_SECRET`.
- `GITHUB_REDIRECT_URI` should be the callback url provided in previous step.

Developer Guide:
https://socialiteproviders.com/GitHub/#installation-basic-usage

https://laravel.com/docs/10.x/socialite#configuration

##### Google

- Go to https://console.cloud.google.com/projectcreate?pli=1 and create new project.
- Go to  https://console.cloud.google.com/apis/credentials/consent and choose External. Fill the required data and
  after that go to https://console.cloud.google.com/apis/credentials to get the credentials.
- Click on `Create
  Credentials` and from the popup choose `OAuth clientID`.
- Set the application type to `Web application` and set the
  `Authorized Redirect URL` to your redirect url for e.g. `localhost/login/google`.
- Next copy the `CLIENT ID` and
  paste into `GOOGLE_CLIENT_ID` and `CLIENT SECRET` into `GOOGLE_CLIENT_SECRET`. `GOOGLE_REDIRECT_URI` should be the
  same as your Authorized Redirect URL.

Developers Guide:
https://socialiteproviders.com/Google-Plus/

##### LinkedIn

- Go to https://www.linkedin.com/developers/apps and create new application.
- Fill the form with the requested data
- Provide your callback url for e.g. `localhost/login/linkedin`
- Copy `Client ID` and paste it into `LINKEDIN_CLIENT_ID`and `Client Secret` into `LINKEDIN_CLIENT_SECRET`.
- `LINKEDIN_REDIRECT_URI` should be the redirect url provided in previous step.
- Go to Products and select `Sign In with LinkedIn`

Developers Guide:
https://socialiteproviders.com/LinkedIn/#linkedin


