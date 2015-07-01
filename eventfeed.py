# all the imports
import os
import collections
import sqlite3
import time
import json
from PIL import Image, ImageFile
#import wx
import pyexiv2
from flask import Flask, request, session, g, redirect, url_for, abort, send_from_directory, render_template, flash, jsonify
from werkzeug.utils import secure_filename

# image globals
UPLOAD_FOLDER = '/var/www/eventfeed/uploads'
ALLOWED_EXTENSIONS = set(['png', 'jpg', 'jpeg', 'gif'])

# PIL's Error "Suspension not allowed here" work around:
# s. http://mail.python.org/pipermail/image-sig/1999-August/000816.html
ImageFile.MAXBLOCK = 1024*1024

# The EXIF tag that holds orientation data.
EXIF_ORIENTATION_TAG = 274

# Obviously the only ones to process are 3, 6 and 8.
# All are documented here for thoroughness.
ORIENTATIONS = {
    1: ("Normal", 0),
    2: ("Mirrored left-to-right", 0),
    3: ("Rotated 180 degrees", 180),
    4: ("Mirrored top-to-bottom", 0),
    5: ("Mirrored along top-left diagonal", 0),
    6: ("Rotated 90 degrees", -90),
    7: ("Mirrored along top-right diagonal", 0),
    8: ("Rotated 270 degrees", -270)
}

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

@app.errorhandler(404)
def page_not_found(e):
    return render_template('404.html'), 404

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

            #rotateImage(os.path.join(app.config['UPLOAD_FOLDER'], filename))
            #img = Image.open(os.path.join(app.config['UPLOAD_FOLDER'], filename))
            #exif_data = img._getexif()
            #flash(exif_data)

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

@app.route('/main/request',methods=['POST'])
def return_main_data():
    db = get_db()
    action = request.form.get('action',None)
    timestamp = request.form.get('timestamp',None)
    if action == 'loadapproved':
        cur = db.execute('select * from posts where status = "approved" order by timestamp asc')
        rows = cur.fetchall()
        posts = []
        for row in rows:
            l = {
                "id":row['id'],
                "timestamp":row['timestamp'],
                "timestamp_modified":row['timestamp_modified'],
                "author":row['author'],
                "text":row['text'],
                "image":row['image'],
                "status":row['status'],
                "type":row['type'],
            }
            posts.append(l)
        return jsonify(posts=posts)
    elif action == 'loadoptions':
        cur = db.execute('select * from options')
        options = cur.fetchall()
        return jsonify(options)
    elif action == 'publishpost':
        text = request.form.get('text', None)
        timestamp_modified = int(time.time())
        db.execute('update posts set status=?, timestamp_modified=? WHERE timestamp=?',('published',timestamp_modified,timestamp))
        db.commit()
        return jsonify(saved='publishpost')
    else:
        flash(action)
        flash(timestamp)
        return render_template('blank.html')

@app.route('/main/')
def show_posts():
    db = get_db()
    cur = db.execute('select * from posts where status = "published" order by timestamp asc')
    posts = cur.fetchall()
    print(posts)
    return render_template('main.html', posts=posts)

@app.route('/moderation/request',methods=['POST'])
def return_moderation_data():
    db = get_db()
    action = request.form.get('action', None)
    timestamp = request.form.get('timestamp', None)

    if action == 'load':
        cur = db.execute('select * from posts where timestamp=?',[timestamp])
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
    elif action == 'edit':
        author = request.form.get('author', None)
        text = request.form.get('text', None)
        timestamp_modified = int(time.time())
        db.execute('update posts set author=?, text=?, timestamp_modified=? WHERE timestamp=?',(author,text,timestamp_modified,timestamp))
        db.commit()
        return jsonify(saved=True)
    elif action == 'approve':
        cur = db.execute('update posts set status=? where timestamp=?',['approved',timestamp])
        db.commit()
        return jsonify(saved=True)
    elif action == 'reject':
        timestamp_modified = int(time.time())
        cur = db.execute('update posts set status=?, timestamp_modified=? where timestamp=?',['rejected',timestamp_modified,timestamp])
        db.commit()
        return jsonify(saved=True)
    elif action == 'savesettings':
        speed = request.form.get('speed', 15000)
        cur = db.execute('update options set speed=? where id=1',[speed])
        db.commit()
        return jsonify(saved=True)
    #return render_template('moderation.html')

@app.route('/moderation/')
@app.route('/moderation/<display>')
def show_moderation(display=None):
    db = get_db()
    if display == 'approved':
        cur = db.execute('select * from posts where status = "approved" order by timestamp asc')
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
        cur = db.execute('select * from options')
        options = cur.fetchall()
        return render_template('moderation.html', options=options, display=display)
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

def fix_orientation(img, save_over=False):
    """
    `img` can be an Image instance or a path to an image file.
    `save_over` indicates if the original image file should be replaced by the new image.
    * Note: `save_over` is only valid if `img` is a file path.
    """
    path = None
    if not isinstance(img, Image.Image):
        path = img
        img = Image.open(path)
    elif save_over:
        raise ValueError("You can't use `save_over` when passing an Image instance. Use a file path instead.")
    try:
        orientation = img._getexif()[EXIF_ORIENTATION_TAG]
    except (TypeError, AttributeError, KeyError):
        raise ValueError("Image file has no EXIF data.")
    if orientation in [3,6,8]:
        degrees = ORIENTATIONS[orientation][1]
        img = img.rotate(degrees)
        if save_over and path is not None:
            try:
                img.save(path, quality=95, optimize=1)
            except IOError:
                # Try again, without optimization (PIL can't optimize an image
                # larger than ImageFile.MAXBLOCK, which is 64k by default).
                # Setting ImageFile.MAXBLOCK should fix this....but who knows.
                img.save(path, quality=95)
        return (img, degrees)
    else:
        return (img, 0)

def rotateImage(infile):
    try:
        # Read Metadata from the image
        metadata = pyexiv2.metadata.ImageMetadata(infile)
        metadata.read();

        # Let's get the orientation
        print metadata
        orientation = metadata.__getitem__("Exif.Image.Orientation")
        print orientation
        """
        orientation = int(str(orientation).split("=")[1][1:-1])

        # Extract thumbnail
        #thumb = metadata.exif_thumbnail

        angle = 0

        # Check the orientation field in EXIF and rotate image accordingly

        # Landscape Left : Do nothing
        if orientation == 1:
            angle = 0
        # Landscape Right : Rotate Right Twice
        elif orientation == 3:
            angle = 180
        # Portrait Normal : Rotate Right
        elif orientation == 6:
            angle = -90
        # Portrait Upside Down : Rotate Left
        elif orientation == 8:
            angle = 90

        # Resetting Exif field to normal
        print "Resetting exif..."
        orientation = 1
        metadata.__setitem__("Exif.Image.Orientation", orientation)
        """
        # Rotate
        """
        if angle != 0:
            # Just rotating the image based on the angle
            print "Rotating image..."
            angle = math.radians(angle)
            img = wx.Image(infile, wx.BITMAP_TYPE_ANY)
            img_centre = wx.Point( img.GetWidth()/2, img.GetHeight()/2 )
            img = img.Rotate( angle, img_centre, True )
            img.SaveFile(infile, wx.BITMAP_TYPE_JPEG)

            # Create a stream out of the thumbnail and rotate it using wx
            # Save the rotated image to a temporary file
            print "Rotating thumbnail..."
            t = wx.EmptyImage(100, 100)
            thumbStream = cStringIO.StringIO(thumb.data)
            t.LoadStream(thumbStream, wx.BITMAP_TYPE_ANY)
            t_centre = wx.Point( t.GetWidth()/2, t.GetHeight()/2 )
            t = t.Rotate( angle, t_centre, True )
            t.SaveFile(infile + ".jpg", wx.BITMAP_TYPE_JPEG)
            thumbStream.close()

            # Read the rotated thumbnail and put it back in the rotated image
        #thumb.data = open(infile + ".jpg", "rb").read();
            # Remove temporary file
        #os.remove(infile + ".jpg")
        """
        # Write back metadata
        metadata.write();

    except Exception, e:
        return False

if __name__ == '__main__':
    #app.run()
    init_db()
    app.run(debug=True, host='0.0.0.0')