=== WpCues Basic Quiz  ===
Contributors: wpcues
Tags: Quiz,exam,test,survey,mobile,certificate,leaderboard,chart,match,multimedia,touch,multiple choice,sort,fill the gaps, quiz maker
Requires at least: 3.5
Tested up to: 4.2.2
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Create math / html / multimedia rich quiz. Award Mozilla Open Badges, Create colorful charts / leader boards and sell your quizzes using stripe.


== Description ==
The plugin tries to approach creating quiz, grading it and issuing certificate,badges and assign levels in the best possible and 
efficient manner. 

### Features ###

* Create quiz with multimedia rich content.
* Add mathematical formulas and symbols easily. You can use latex or mathml. The math symbols will be displayed with the help of mathjax.
* Categorize your quizzes and questions in custom categories.
* Randomize Questions and answers
* Multiple type of Questions such as single /multiple choice, sorting, fill the gaps, true false, open ended, Matching column
* Award partial marking in question having multiple correct answer
* Assign Grades on the basis of point scored or percentage of correct answers replied. Issue different html / pdf certificate on the basis of grade.
* Configure final result screen with multiple template variables. Easily add Social share button or show report on the final result screen.
* Add Google Recaptcha to your quiz
* Create great leaderboards of different types and show them using shortcode.
* Create bar, line and pie chart and include anywhere using shortcode.
* Award Mozilla open  badges and assign different levels to your users.
* Detailed statistics related to your quizzes
* Sell your quizzes using Stripe.
* Email notification to user / admin / editor (quiz creator) when someone takes quiz /attains new level /get issued new badge.
 
### PDF Certificates ###
 * To show pdf certificates, you need mpdf library . You can download it from [official mpdf repository](http://www.mpdf1.com/mpdf/index.php?page=Download).
 * Place it inside 'lib' subfolder inside public folder and you are ready to publish stylish pdf certificate to your users.
 * The reason that I have not included that library by default is because of it's size (53 MB) as it can cause some unforseen problem while plugin activation hampering basic usage.
 
 ### Online Demo ###
 * Feel free to check the [live demo here](https://www.wpcues.com/wpcuesbasicquiz/wp-admin/ "Live demo").
 userid : demo
 password : wpcuestrial123!
 
== Installation ==
* Navigate to Add New Plugin page within your WordPress
* Search for WpCues Basic Quiz
* Click Install Now link on the plugin and follow the prompts
* Activate the plugin through the 'Plugins' menu in WordPress

**Or**

* Upload plugin dir 'WpCue Basic Quiz' to the `/wp-content/plugins/` directory
* Activate the plugin through the 'Plugins' menu in WordPress


== Frequently Asked Questions ==

= How are grades calculated? =

WpCues Quiz computes the grade either on basis of total points collected or percentage correct answer scored  by the visitor. Then it finds the grade. For example: If you have 2 questions and the correct answers in them give 5 points each, the visitor will collect either 0, or 5 or 10 points at the end. You may decide to define grades "Failed" for 0 to 4 points and "Passed" for those who collected more than 4 points. In reality you are going to have more questions and answers and some answers may be partly correct which gives you full flexibility in assigning points and managing the grades.
You can also issue certificate on the basis of grade attained by the user.


= How do I show the quiz to the visitors of my blog? =

You need to create a post and embed the quiz short code. The quiz short code is shown in a column on Quiz table. Also you can directly insert from add/edit quiz form by clicking 'Insert into post' button.
You can place as many as exam shortcode as you want in a post /page or anywhere (even in widget in sidebar).

= How to translate the plugin interface in my language =

You can use the standard WordPress way of translating plugins (via Poedit and .po / .mo files).
If using Poedit, your file names should start with "watu-". For example: watu-de_DE.po / watu-de_DE.mo. They should be placed in wp-content/languages/plugins folder.

== Screenshots ==

1. List of your quizzes with shortcodes for embedding in posts or pages
2. The form for creating and editing a quiz
3. The form to add / edit question
4. The form to add new grade

== Changelog ==
### Changes in 1.6.5 ###
* Fixed code for Certificates (both html and pdf certificates)
* Fixed code for statistics page

### Changes in 1.6.4 ##
* Changed code structure to be OOP based
### Changes in 1.6.3 ###
* Added code changes required for open ended, Sorting and fill the gaps questions

### Changes in 1.6.2 ###
* Fixed minor bugs

### Changes in 1.6.1 ###
* Fixed minor bug for quiz shortcode

### Changes in 1.6 ###
* Fixed bug related to leader board , chart and product
* Fixed bug related to display of mathematical formulas correctly
* Changed default report formatting
* Fixed bug related to selecting only a fixed number of questions out of total questions added in a quiz
* Complete internationalization of JavaScript files

### Changes in 1.5 ###
* Fixed bug related to open ended question
* Fixed some minor bugs related to statistics admin page
* Add new feature to add captcha to quiz

### Changes in 1.4 ###
* Removed Bugs to add gradegroup for a quiz and display point on final screen correctly 
* Removed bug to delete log record 

### Changes in 1.3 ###
* Fixed some bugs related to adding question after quiz have been published

### Changes in 1.2 ###
* Fixed activation bug for PHP < 5.3

### Changes in 1.1 ###
* Changed Quiz default post type to private and added setting to change it to public with proper slug.
* Minor Code fixes and improvements