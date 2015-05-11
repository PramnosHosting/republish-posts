# republish-posts
Allows a Wordpress administrator to Schedule a post to be republished.
It adds a new meta box in post edit page that allows you to set the date and
time. A cron job runs every hour and checks for post to be republished.

When a post is set to be republished, it changes it's date to current date,
changes it's status to draft and then to published again (to let all actions
hooked to the post being published run).
