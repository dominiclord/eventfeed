# all the imports
import os
import sqlite3

import time
import json
import collections
from flask import Flask, request, session, g, redirect, url_for, abort, send_from_directory, render_template, flash, jsonify
from werkzeug.utils import secure_filename
from PIL import Image

# image globals
UPLOAD_FOLDER = '/var/www/eventfeed/uploads'
ALLOWED_EXTENSIONS = set(['png', 'jpg', 'jpeg', 'gif'])

# create our application
app = Flask(__name__)
app.config.from_object(__name__)
app.config['UPLOAD_FOLDER'] = UPLOAD_FOLDER


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

def allowed_file(filename):
    return '.' in filename and \
           filename.rsplit('.', 1)[1] in ALLOWED_EXTENSIONS

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

@app.route('/uploads/<filename>')
def uploaded_file(filename):
    return send_from_directory(app.config['UPLOAD_FOLDER'],
                               filename)

@app.route('/submit', methods=['POST'])
def submit_post():
#    if not session.get('logged_in'):
#        abort(401)
    if request.method == 'POST':

        timestamp = int(time.time())
        image = False
        filename = ''
        posttype = ''
        text = ''

        if request.form.get('author'):
            author = request.form['author']

        if request.form.get('text'):
            text = request.form['text']

        file = request.files['imagefile']
        if file and allowed_file(file.filename):
            image = True
            filename = secure_filename(file.filename)
            filename = str(timestamp) + '.' + filename.rsplit('.', 1)[1]
            file.save(os.path.join(app.config['UPLOAD_FOLDER'], filename))
            flash(filename)

        if image == True and text !='':
            posttype = 'hybrid'
        elif image == True and text =='':
            posttype = 'image'
        elif image == False and text !='':
            posttype = 'text'

        db = get_db()
        db.execute('insert into posts (timestamp, author, text, image, status, type) values (?, ?, ?, ?, ?, ?)',
            [timestamp, author, text, filename, 'moderation', posttype]
        )
        db.commit()
        flash('New post was successfully sent')
        #return redirect( url_for('user_form',success='success') )
        return render_template('user_form.html')

@app.route('/main')
def show_posts():
    db = get_db()
    cur = db.execute('select * from posts where status = "published" order by timestamp asc')
    posts = cur.fetchall()
    return render_template('main.html', posts=posts)

@app.route('/moderation/request',methods=['GET','POST'])
def return_data():
    db = get_db()
    #state = request.args['state']
    #timestamp = request.args['timestamp']
    state = request.form['state']
    timestamp = request.form['timestamp']

    if state == 'load':
        cur = db.execute('select * from posts where timestamp = ?',[timestamp])
        posts = cur.fetchall()
        post = posts[0]
        return jsonify(
            id=post['id'],
            timestamp = post['timestamp'],
            timestamp_modified = post['timestamp_modified'],
            author = post['author'],
            text = post['text'],
            image = post['image'],
            status = post['status'],
            type = post['type']
        )

@app.route('/moderation/')
@app.route('/moderation/<display>')
# @app.route('/moderation?<success>')
def show_moderation(display=None):
    db = get_db()
    if display == 'approved':
        cur = db.execute('select * from posts where status = "approuver" order by timestamp asc')
        posts = cur.fetchall()
        return render_template('moderation.html', posts=posts, display=display)
    elif display == 'published':
        cur = db.execute('select * from posts where status = "published" order by timestamp_modified desc')
        posts = cur.fetchall()
        return render_template('moderation.html', posts=posts, display=display)
    elif display == 'rejected':
        cur = db.execute('select * from posts where status = "rejected" order by timestamp desc')
        posts = cur.fetchall()
        return render_template('moderation.html', posts=posts, display=display)
    elif display == 'options':
        cur = db.execute('select * from params')
        params = cur.fetchall()
        return render_template('moderation.html', params=params, display=display)
    else:
        cur = db.execute('select * from posts where status = "moderation" order by timestamp asc')
        posts = cur.fetchall()
        return render_template('moderation.html', posts=posts, display=display)
    #im=Image.open(filepath)
    #m.size # (width,height) tuple

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