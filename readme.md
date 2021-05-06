# OurJobFlow Overview
OurJobFlow is a Job Portal which has a following things to focus on :
1. Guest
2. Employer.
3. JobSeeker.
4. Admin.

## Installation Guide

1. Use the package manager [composer](https://getcomposer.org/download/) to install Dependency of OurJobFlow.
2. PHP Version Needed and Apache Needed [xampp](https://www.apachefriends.org/download.html) to download and Install.
3. After Downloading need a repo [Gitlab](https://gitlab.com/faheemhasan/ourjobflow) for this you need a correct access rights.
4. After cloning it put it in your htdocs folder
5. After geeting this you need to do the composer install command.
6. migrate the database.
7. install the passport by using cmd.
8. Clean the cache if required.
9. copy the env files from server and put in local env.
10. Then find the updated Postman collection here [Postman](https://www.getpostman.com/collections/e850bbea48a108e42522) here you have the updated collection.


```bash
composer install
```

```bash
php artisan migrate
```
```bash
php artisan passport:install
```

```bash
php artisan cache:clean
```

## Structure Of a Code

1. Routes you will find in [Routes](https://gitlab.com/faheemhasan/ourjobflow/-/blob/development/development/routes/api.php), Are the API's Routes.
2. Controller you'll fidn in [Controller](https://gitlab.com/faheemhasan/ourjobflow/-/tree/development/development/app/Http/Controllers/API), Are the API's Controller.
3. Model you'll find in [Models](https://gitlab.com/faheemhasan/ourjobflow/-/tree/development/development/app/Models), Are the API's Model.

## Usage
```Laravel
User Model

protected $appends = ['image_url']; # this is to add another key to get the Image Url

    public function getImageUrlAttribute()
    {
        return  env('APP_URL').'/storage/user_images/'.$this->image;
    }

Relation are done using with
  public function resume()
    {
        return $this->hasMany('App\Models\Resume','user_id');
    }

Resume Model

 protected $appends = ['full_resume_url']; # this is used to add another Key to ge the Resume Url

    public function getFullResumeUrlAttribute()
    {
        return env('APP_URL').'/storage/user_resume/'.$this->resume_name;
    }

    Relation are done using with
  public function resume()
    {
        return $this->hasMany('App\Models\Resume','user_id');
    }
```
## Project Status
Sprint's Completed are 1,2,3,5 Link are [SprintSheet](https://docs.google.com/spreadsheets/d/101pbjXLq-RwY5grtFKkkfy1BennR9mDhczc_ijpYtwk/edit#gid=1747869372) please have a correct access right to access it.

Design's Link are [Design](https://xd.adobe.com/view/d7a01fc1-5266-4749-58a8-3e65c1703c0f-a191/) please have a correct access right to access it.

Then find the updated Postman collection here [Postman](https://www.getpostman.com/collections/e850bbea48a108e42522) here you have the updated collection.

## Use Case
1. Guest has only access to search a job.
2. Jobsseker -> all the API's are prefix with jobseeker and only hows user_type is 1 will have an access to this.
3. Employeer -> all the API's are prefix with Employeer and only hows user_type is 2 will have an access to this.
4. Admin -> all the API's are prefix with Admin and only hows user_type is 3 will have an access to this.

## Contributing
Contributed by Saurabh Nema.

## Working Url
1. API's working Url [API's]( http://139.59.82.244/ourjobflow/development/), Then add the /api/v1/ (^ your callig urls&).
2. FrontEnd Working Url [URLS]( http://139.59.82.244/),

3. Database Working URL [Database]( http://139.59.82.244/phpmyadmin). Please have a correct access.

## License
[MIT](https://choosealicense.com/licenses/mit/)