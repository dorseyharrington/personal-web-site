; config file for Seven Kevins MVC framework

[application]
default_controller = index
default_action = index
error_controller = error404
error_reporting = E_ALL
display_errors = 1
language = en
timezone = "America/New_York"
site_name = "Dorsey Harrington"
version = 1.0.0
currency = USD
domain = multi

[pagination]
items_per_page = 10;

[database]
db_type = mysql
db_name = wwwdorse_DORSEYHARRINGTON
db_hostname = localhost
db_username = wwwdorse_admin
db_password = cyclops
db_port = 3306

[template]
template_dir = "templates"
cache_dir = "/tmp/cache"
cache_lifetime = 3600

[mail]
mailer_type = system
admin_email = dorseyharrington@verizon.net
admin_name = "T. Dorsey Harrington"
admin_city = "Gillette, NJ"
smtp_server = mail.example.com 
smtp_port = 25;
x_mailer = "PHPRO.ORG Mail"
smtp_server = "mail.example.com"
smtp_port = 25
smtp_timeout = 30

[logging]
log_level = 200
log_handler = file
log_file = /tmp/dorseyharrington.log

[css]
; style sheets - list these in the desired order of appearance
style[] = reset
style[] = rebuild
style[] = style
style[] = lightbox
;style[] = thickbox
;style[] = galleriffic/galleriffic-2
style[] = galleryview/css/jquery.galleryview-3.0
;style[] = ad-gallery/jquery.ad-gallery
;style[] = jquery.gallery
style[] = shine

[js]
; javascript files - list these in the desired order of appearance
filename[] = lightbox
;filename[] = thickbox
filename[] = ajaxfileupload
filename[] = jqproject
;filename[] = galleriffic/jquery.galleriffic
;filename[] = galleriffic/jquery.opacityrollover
filename[] = galleryview/js/jquery.timers-1.2
filename[] = galleryview/js/jquery.easing.1.3
filename[] = galleryview/js/jquery.galleryview-3.0
;filename[] = ad-gallery/jquery.ad-gallery
;filename[] = jquery.gallery.0.3.min

[images]
PROJECT_PATH = "/home/wwwdorse/public_html/projects"
PROJECT_URL = "http://www.dorseyharrington.com/projects"
imagemagickpath = "/usr/bin/convert"
;aspect_ratio = 35mm
aspect_ratio = digital
;aspect_ratio = square
header_image = "dorseyharrington_2.jpg"

[facebook]
; set like = 1 to include a Facebook Like button in the gallery
like = 1
; comma-separated list of Facebook IDs of page administrators, or a platform application ID
admins = "dorseyharrington@verizon.net"
pagecontent = "Gallery of Personal Projects"
pagetitle = "Dorsey&rsquo;s Projects"