function isset(elm) {
	return (typeof(elm) != 'undefined' && elm != null);
}

function Ajax(elm, action) {
	this.post = new Array();
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
	}
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
				else {
					alert(m_error_ajax);
				}
				ajax.cancel();
			}
		};
	}
	this.cancel = function() {
		if (this.loader) {
			this.loader.parentNode.replaceChild(this.elm, this.loader);
		}
	}
}

function onclick_logout() {
	document.getElementById("form-logout").submit();
}

function onclick_load(elm, pms) {
	var ajax = new Ajax(elm, 'load');
	var last_link = document.querySelectorAll(".div-link");
	if (last_link.length > 0) {
		ajax.addParam('id', last_link[last_link.length-1].id.split('-')[1]);
	}
	if (typeof feed != 'undefined') { ajax.addParam('feed', feed); }
	if (typeof tag != 'undefined') { ajax.addParam('tag', tag); }
	if (typeof q != 'undefined') { ajax.addParam('q', q); }
	if (typeof pms.type != 'undefined') { ajax.addParam('type', pms.type); }
	ajax.send(function(ans) {
		var div = document.createElement("div");
		div.innerHTML = ans['html'];
		var after = document.querySelector(".p-more");
		after.parentNode.insertBefore(div, after);
	}, function(ans) {
		ajax.elm = document.createElement("span");
		ajax.elm.innerHTML = m_no_more_link;
	});
}

function onclick_refresh(elm, pms) {
	var ajax = new Ajax(elm, 'refresh')
	if (typeof tag != 'undefined') { ajax.cancel(); return false; }
	if (typeof q != 'undefined') { ajax.cancel(); return false; }
	if (typeof feed != 'undefined') { ajax.addParam('feed', feed); }
	ajax.send(function(ans) {
		var div = document.createElement("div");
		div.innerHTML = ans['html'];
		var first_link = document.querySelector(".div-link");
		if (!first_link) { var first_link = document.querySelector(".p-more"); }
		first_link.parentNode.insertBefore(div, first_link);
	});
}

function onclick_allRead(elm, pms) {
	var ajax = new Ajax(elm, 'read');
	var ids = new Array();
	var links = document.querySelectorAll(".div-link");
	for (var i = 0; i < links.length; i++) {
		ids.push(links[i].id.split('-')[1]);
	}
	ajax.addParam('ids', ids.join(','));
	ajax.send(function(ans) {
		for (var id in ans['ids']) {
			var link = document.getElementById("link-"+id);
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
	var ids = new Array();
	var links = document.querySelectorAll(".div-link");
	for (var i = 0; i < links.length; i++) {
		ids.push(links[i].id.split('-')[1]);
	}
	ajax.addParam('ids', ids.join(','));
	ajax.send(function(ans) {
		for (var id in ans['ids']) {
			var link = document.getElementById("link-"+id);
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
		comment: document.getElementById('comment')
	};
	var old_title = obj.title.innerHTML;
	var old_content = obj.content.innerHTML;
	var save = obj.actions[1].querySelectorAll('a')[0];
	var cancel = obj.actions[1].querySelectorAll('a')[1];
	obj.title.setAttribute('contenteditable', true);
	obj.content.setAttribute('contenteditable', true);
	obj.tags.style.display = 'none';
	obj.comment.style.display = 'none';
	obj.url.style.display = 'none';
	form.comment.style.display = 'block';
	form.tags.style.display = 'block';
	obj.actions[0].style.display = 'none';
	obj.actions[1].style.display = 'block';
	save.onclick = function() {
		var ajax = new Ajax(save, 'edit');
		ajax.addParam('id', pms.id);
		ajax.addParam('title', obj.title.innerHTML);
		ajax.addParam('content', obj.content.innerHTML);
		ajax.addParam('tags', form.tags.value);
		ajax.addParam('comment', form.comment.value);
		ajax.send(function(ans) {
			old_title = ans['title'];
			old_content = ans['content'];
			cancel.click();
			obj.tags.innerHTML = ans['tags_list'];
			obj.comment.innerHTML = ans['comment'];
			form.tags.value = ans['tags'].join(', ');
			form.comment.value = ans['comment'];
		});
	};
	cancel.onclick = function() {
		obj.title.setAttribute('contenteditable', false);
		obj.content.setAttribute('contenteditable', false);
		obj.title.innerHTML = old_title;
		obj.content.innerHTML = old_content;
		obj.tags.style.display = 'inline';
		obj.comment.style.display = 'block';
		obj.url.style.display = 'block';
		form.tags.style.display = 'none';
		form.comment.style.display = 'none';
		obj.actions[0].style.display = 'block';
		obj.actions[1].style.display = 'none';
	};
}



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
		var div = document.getElementById("feed-"+pms.id);
		div.parentNode.removeChild(div);
	});
}

function onclick_js_add(elm, pms) {
	alert(m_add_popup);
	document.getElementById('js_add').style.display = 'block';
}