function isset(elm) {
	return (typeof(elm) != 'undefined' && elm !== null);
}

function Ajax(elm, action) {
	this.post = [];
	this.elm = undefined;
	this.loader = undefined;
	if (elm) {
		this.elm = elm;
		this.loader = document.createElement('span');
		this.loader.className = 'loading';
		this.loader.innerHTML = '<i class="n1"></i><i class="n2"></i><i class="n3"></i>';
		this.elm.parentNode.replaceChild(this.loader, this.elm);
	}
	this.post.push('action='+action);
	this.post.push('page='+page);
	this.addParam = function(name, value) {
		this.post.push(name+'='+encodeURIComponent(value));
	};
	this.send = function(callback_success, callback_error) {
		var ajax = this;
		var xhr = new XMLHttpRequest();
		xhr.open('POST', ajax_url);
		xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		xhr.send(this.post.join('&'));
		xhr.onreadystatechange = function() {
			if (xhr.readyState == xhr.DONE) {
				if (xhr.status == 200) {
					var ans = JSON.parse(xhr.responseText);
					if (ans['status'] == 'success') {
						if (typeof callback_success != 'undefined') {
							callback_success(ans);
						}
					}
					else {
						if (typeof callback_error != 'undefined') {
							callback_error(ans);
						}
					}
				}
				else if (xhr.status == 403) {
					alert(m_error_login);
				}
				else {
					alert(m_error_ajax);
				}
				ajax.cancel();
			}
		};
	};
	this.cancel = function() {
		if (this.loader) {
			this.loader.parentNode.replaceChild(this.elm, this.loader);
		}
	};
}

function onclick_logout() {
	document.getElementById('form-logout').submit();
}

function onclick_load(elm, pms) {
	var ajax = new Ajax(elm, 'load');
	var last_link = document.querySelectorAll('.div-link');
	if (last_link.length > 0) {
		ajax.addParam('id', last_link[last_link.length-1].id.split('-')[1]);
	}
	if (typeof feed != 'undefined') { ajax.addParam('feed', feed); }
	if (typeof tag != 'undefined') { ajax.addParam('tag', tag); }
	if (typeof q != 'undefined') { ajax.addParam('q', q); }
	if (typeof pms.type != 'undefined') { ajax.addParam('type', pms.type); }
	ajax.send(function(ans) {
		var div = document.createElement('div');
		div.innerHTML = ans['html'];
		var after = document.querySelector('.p-more');
		after.parentNode.insertBefore(div, after);
	}, function(ans) {
		ajax.elm = document.createElement('span');
		ajax.elm.innerHTML = m_no_more_link;
	});
}

function onclick_refresh(elm, pms) {
	var ajax = new Ajax(elm, 'refresh');
	if (typeof tag != 'undefined') { ajax.cancel(); return false; }
	if (typeof q != 'undefined') { ajax.cancel(); return false; }
	if (typeof feed != 'undefined') { ajax.addParam('feed', feed); }
	ajax.send(function(ans) {
		var div = document.createElement('div');
		div.innerHTML = ans['html'];
		var first_link = document.querySelector('.div-link');
		if (!first_link) { first_link = document.querySelector('.p-more'); }
		first_link.parentNode.insertBefore(div, first_link);
	});
}

function onclick_allRead(elm, pms) {
	var ajax = new Ajax(elm, 'read');
	var ids = [];
	var links = document.querySelectorAll('.div-link');
	for (var i = 0; i < links.length; i++) {
		ids.push(links[i].id.split('-')[1]);
	}
	ajax.addParam('ids', ids.join(','));
	ajax.send(function(ans) {
		for (var id in ans['ids']) {
			var link = document.getElementById('link-'+id);
			if (page == 'links') {
				link.querySelector('h2').className = '';
				link.querySelector('.a-read').style.display = 'none';
				link.querySelector('.a-unread').style.display = 'inline-block';
			}
			else {
				var parent = link.parentNode;
				parent.removeChild(link);
			}
		}
	});
}
function onclick_allClear(elm, pms) {
	if (!confirm(m_confirm_clear)) { return false; }
	var ajax = new Ajax(elm, 'clear');
	var ids = [];
	var links = document.querySelectorAll('.div-link');
	for (var i = 0; i < links.length; i++) {
		ids.push(links[i].id.split('-')[1]);
	}
	ajax.addParam('ids', ids.join(','));
	ajax.send(function(ans) {
		for (var id in ans['ids']) {
			var link = document.getElementById('link-'+id);
			link.parentNode.removeChild(link);
		}
	});
}

function onclick_read(elm, pms) {
	var ajax = new Ajax(elm, 'read');
	ajax.addParam('ids', pms.id);
	ajax.send(function(ans) {
		if (typeof ans['ids'][pms.id] == 'undefined') { return false; }
		var link = document.getElementById('link-'+pms.id);
		if (page == 'home') {
			link.parentNode.removeChild(link);
		}
		else {
			if (page == 'links') { link.querySelector('h2').className = ''; }
			elm.style.display = 'none';
			link.querySelector('.a-unread').style.display = 'inline-block';
		}
	});
}
function onclick_unread(elm, pms) {
	var ajax = new Ajax(elm, 'unread');
	ajax.addParam('ids', pms.id);
	ajax.send(function(ans) {
		if (typeof ans['ids'][pms.id] == 'undefined') { return false; }
		var link = document.getElementById('link-'+pms.id);
		if (page == 'links') { link.querySelector('h2').className = 'unread'; }
		elm.style.display = 'none';
		link.querySelector('.a-read').style.display = 'inline-block';
	});
}
function onclick_archive(elm, pms) {
	var ajax = new Ajax(elm, 'archive');
	ajax.addParam('ids', pms.id);
	ajax.send(function(ans) {
		if (typeof ans['ids'][pms.id] == 'undefined') { return false; }
		var link = document.getElementById('link-'+pms.id);
		if (page == 'home') {
			link.parentNode.removeChild(link);
		}
		else {
			if (page == 'links') { link.querySelector('h2').className = ''; }
			link.querySelector('.a-read').style.display = 'none';
			link.querySelector('.a-unread').style.display = 'none';
			elm.style.display = 'none';
		}
	});
}
function onclick_delete(elm, pms) {
	if (!confirm(m_confirm_delete)) { return false; }
	var ajax = new Ajax(elm, 'delete');
	ajax.addParam('ids', pms.id);
	ajax.send(function(ans) {
		if (typeof ans['ids'][pms.id] == 'undefined') { return false; }
		if (page == 'link') {
			window.location.href = home_url;
		}
		else {
			var div = document.getElementById('link-'+pms.id);
			div.parentNode.removeChild(div);
		}
	});
}
function onclick_edit(elm, pms) {
	var obj = {
		title: document.querySelector('h1'),
		content: document.querySelector('.div-content'),
		tags: document.querySelector('.tags'),
		comment: document.querySelector('.div-comment'),
		url: document.querySelector('.p-url'),
		actions: document.querySelectorAll('.div-actions')
	};
	var form = {
		tags: document.getElementById('tags'),
		editTags:  document.querySelector('.editTags'),
		editor: document.querySelector('.div-edit')
	};
	var old_title = obj.title.innerHTML;
	var old_content = obj.content.innerHTML;
	var old_comment = obj.comment.innerHTML;
	var old_tags = form.tags.value;
	var save = obj.actions[1].querySelectorAll('a')[0];
	var cancel = obj.actions[1].querySelectorAll('a')[1];
	obj.title.setAttribute('contenteditable', true);
	obj.content.setAttribute('contenteditable', true);
	obj.comment.setAttribute('contenteditable', true);
	obj.tags.style.display = 'none';
	obj.url.style.display = 'none';
	form.editTags.style.display = 'inline';
	form.editTags.querySelector('span').innerHTML = '';
	form.tags.onupdate();
	form.editor.style.display = 'block';
	obj.actions[0].style.display = 'none';
	obj.actions[1].style.display = 'block';
	save.onclick = function() {
		var ajax = new Ajax(save, 'edit');
		ajax.addParam('id', pms.id);
		ajax.addParam('title', obj.title.innerHTML);
		ajax.addParam('content', obj.content.innerHTML);
		ajax.addParam('tags', form.tags.value);
		ajax.addParam('comment', obj.comment.innerHTML);
		ajax.send(function(ans) {
			old_title = ans['title'];
			old_content = ans['content'];
			old_comment = ans['comment'];
			old_tags = ans['tags'].join(', ');
			cancel.click();
			obj.tags.innerHTML = ans['tags_list'];
		});
	};
	cancel.onclick = function() {
		obj.title.setAttribute('contenteditable', false);
		obj.content.setAttribute('contenteditable', false);
		obj.comment.setAttribute('contenteditable', false);
		obj.title.innerHTML = old_title;
		obj.content.innerHTML = old_content;
		obj.comment.innerHTML = old_comment;
		obj.tags.style.display = 'inline';
		obj.url.style.display = 'block';
		form.editTags.style.display = 'none';
		form.tags.value = old_tags;
		form.editor.style.display = 'none';
		obj.actions[0].style.display = 'block';
		obj.actions[1].style.display = 'none';
	};
}

function onload_tags() {
	var editTags = document.querySelector('.editTags');
	var list = editTags.querySelector('span');
	var tags = document.getElementById('tags');
	var addTag = document.getElementById('addTag');
	var pick = document.querySelector('.pick-tag');
	var pick_tags = pick.querySelectorAll('span');
	function update_tags_input() {
		var as = editTags.querySelectorAll('.tag');
		var arr = [];
		for (var i=0; i<as.length; i++) { arr.push(as[i].innerHTML); }
		tags.value = arr.join(',');
	}
	function append_tag(tag) {
		var a = document.createElement('a');
		a.href = '#';
		a.className = 'tag';
		a.innerHTML = tag;
		a.onclick = remove_tags;
		list.appendChild(a);
	}
	function add_tag() {
		if (addTag.value !== '') {
			append_tag(addTag.value);
			addTag.value = '';
			update_tags_input();
		}
	}
	function remove_tags(e) {
		e.srcElement.parentNode.removeChild(e.srcElement);
		update_tags_input();
		return false;
	}
	function update_tags() {
		if (tags.value !== '') {
			var arr = tags.value.split(/,/);
			for (var i=0; i<arr.length; i++) { append_tag(arr[i]); }
		}
	}
	var keepFocus = false;
	pick.onmousedown = function(e) {
		if (e.srcElement.className == 'visible') {
			// on n'a pas cliqué sur la barre de défilemenent ni sur la bordure
			// mais bien sur un nom de tag
			addTag.value = e.srcElement.innerHTML;
			add_tag();
			keepFocus = true; // on veut que addTag garde le focus
		}
	};
	addTag.onkeydown = function(e) {
		if ((('keyCode' in e) && (e.keyCode == 13 || e.keyCode == 188)) ||
			(('key' in e) && (e.key == 'Enter' || e.key == ','))) {
			add_tag();
			addTag.blur();
			addTag.focus();
			return false;
		}
		if (('keyCode' in e && e.keyCode == 9) ||
			('key' in e && e.key == 'Tab')) {
			var elm = form.list.querySelector('.visible');
			if (elm !== null) {
				// on récupère le premier élément de la liste déroulante
				addTag.value = elm.innerHTML;
				add_tag();
				addTag.blur();
				addTag.focus();
			}
			return false;
		}
	};
	addTag.onfocus = function() {
		var pos = addTag.getBoundingClientRect();
		pick.style.left = pos.left+'px';
		pick.style.top = pos.bottom+'px';
		addTag.onkeyup(); // On initialise la liste en fonction de addTag
	};
	addTag.onblur = function() {
		if (!keepFocus) {
			pick.style.left = '-9999px';
			pick.style.top = '-9999px';
		}
		else {
			keepFocus = false;
			addTag.focus();
		}
	};
	addTag.onkeyup = function() {
		var val = addTag.value;
		for (var i=0; i<pick_tags.length; i++) {
			if (pick_tags[i].innerHTML.indexOf(val) === -1) {
				pick_tags[i].className = '';
			}
			else {
				pick_tags[i].className = 'visible';
			}
		}
	};
	tags.onupdate = update_tags;
	update_tags();
}
if (document.querySelector('.editTags') !== null) { onload_tags(); }


function onclick_clear_feed(elm, pms) {
	if (!confirm(m_confirm_clear_f)) { return false; }
	var ajax = new Ajax(elm, 'clear_feed');
	ajax.addParam('feed', pms.id);
	ajax.send();
}
function onclick_delete_feed(elm, pms) {
	if (!confirm(m_confirm_delete_f)) { return false; }
	var ajax = new Ajax(elm, 'delete_feed');
	ajax.addParam('feed', pms.id);
	ajax.send(function() {
		var div = document.getElementById('feed-'+pms.id);
		div.parentNode.removeChild(div);
	});
}

function onclick_js_add(elm, pms) {
	alert(m_add_popup);
	document.getElementById('js_add').style.display = 'block';
}

function onclick_formate(elm, pms) {
	if (pms.cmd == 'link') {
		var link = prompt(m_enter_url, 'http://');
		if (link && link != 'http://') {
			return document.execCommand('createlink', false, link);
		}
	}
	if (pms.cmd == 'image') {
		var img = prompt(m_enter_url, 'http://');
		if (img && img != 'http://') {
			return document.execCommand('insertImage', false, img);
		}
	}
	else if (typeof pms.value != 'undefined') {
		return document.execCommand(pms.cmd, false, pms.value);
	}
	else {
		return document.execCommand(pms.cmd, false, null);
	}
	return false;
}