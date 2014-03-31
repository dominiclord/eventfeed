# all the imports
import os
import sqlite3

import time
from flask import Flask, request, session, g, redirect, url_for, abort, \
     render_template, flash

# create our application
app = Flask(__name__)
app.config.from_object(__name__)

# Load default config and override config from an environment variable
app.config.update(dict(
    DATABASE=os.path.join(app.root_path, 'eventfeed.db'),
    DEBUG=True,
    SECRET_KEY='youputyourheadinsidethisturkey',
    USERNAME='admin',
    PASSWORD='default'
))
app.config.from_envvar('FLASKR_SETTINGS', silent=True)

# Connect to DB function
def connect_db():
    """Connects to the specific database."""
    rv = sqlite3.connect(app.config['DATABASE'])
    rv.row_factory = sqlite3.Row
    return rv

def init_db():
    with app.app_context():
        db = get_db()
        with app.open_resource('schema.sql', mode='r') as f:
            db.cursor().executescript(f.read())
        db.commit()

def get_db():
    """Opens a new database connection if there is none yet for the current application context."""
    if not hasattr(g, 'sqlite_db'):
        g.sqlite_db = connect_db()
    return g.sqlite_db

@app.teardown_appcontext
def close_db(error):
    """Closes the database again at the end of the request."""
    if hasattr(g, 'sqlite_db'):
        g.sqlite_db.close()

@app.route('/')
# @app.route('/success')
def user_form(success=None):
    success = request.args.get('success')

    return render_template('user_form.html',success=success)

@app.route('/submit', methods=['POST'])
def submit_post():
#    if not session.get('logged_in'):
#        abort(401)
    db = get_db()
    db.execute('insert into posts (timestamp, author, text, image, status, type) values (?, ?, ?, ?, ?, ?)',
        [int(time.time()), request.form['author'], request.form['text'], 'test', 'moderation', 'type']
    )
    db.commit()
    flash('New post was successfully sent')
    return redirect( url_for('user_form',success='success') )

@app.route('/main')
def show_posts():
    db = get_db()
    cur = db.execute('select * from posts where status = "published" order by timestamp asc')
    posts = cur.fetchall()
    return render_template('main.html', posts=posts)

@app.route('/moderation')
@app.route('/moderation/<display>')
# @app.route('/moderation?<success>')
def show_moderation(display=None):
    db = get_db()
    cur = db.execute('select * from posts where status = "published" order by timestamp asc')
    posts = cur.fetchall()
    return render_template('moderation.html', posts=posts, display=display)

@app.route('/login', methods=['GET', 'POST'])
def login():
    error = None
    if request.method == 'POST':
        if request.form['username'] != app.config['USERNAME']:
            error = 'Invalid username'
        elif request.form['password'] != app.config['PASSWORD']:
            error = 'Invalid password'
        else:
            session['logged_in'] = True
            flash('You were logged in')
            return redirect(url_for('show_moderation'))
    return render_template('login.html', error=error)

@app.route('/logout')
def logout():
    session.pop('logged_in', None)
    flash('You were logged out')
    return redirect(url_for('login'))


if __name__ == '__main__':
    #app.run()
    init_db()
    app.run(debug=True, host='0.0.0.0')