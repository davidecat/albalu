let SS88_MediaLibraryFileSize = {};
let SS88_MLFS_Data = window.ss88MLFS || window.ss88 || { ajax_url: window.ajaxurl || '', nonce: '' };

function SS88_MediaLibraryFileSize_init_MediaLibrary() {

	SS88_MediaLibraryFileSize = {

			init: ()=>{

				SB.initGridModal();
				SB.indexCheck();
				SB.addReindexButton();
				SB.initVariantsModal();
			
		},
		indexCheck: ()=> {
			
			fetch(SS88_MLFS_Data.ajax_url + '?' + new URLSearchParams({ action: 'SS88MLFS_indexCount', nonce: SS88_MLFS_Data.nonce })).then(function(response) {

				return response.json();

			}).then(function(response) {

				SB.addButton()

				if(response.data.TotalMLSize) SB.addSize(response.data.TotalMLSize, response.data.TotalMLSize_Title)

			}).catch( err => { console.log(err); SB.sendAlert('error', err.message); } );
			
		},
        addButton: ()=> {

			if(document.querySelector('.ss88indexmedia')) return;

			let cmBtn = (window.location.href.includes('&ss88first')) ? '<div class="ss88arrow">Click me!</div>' : '';

            var div = document.createElement('div');
            div.innerHTML = '<button href="#" class="page-title-action ss88indexmedia" data-orig="Index Media">Index Media'+ cmBtn +'</button>';

			if(document.querySelector('hr.wp-header-end')) {

				document.querySelector('hr.wp-header-end').before(div.firstChild);

			}
			else if(document.querySelector('h2')) {

				div.innerHTML = '<button href="#" class="add-new-h2 ss88indexmedia" data-orig="Index Media">Index Media</button>';
				document.querySelector('h2').appendChild(div.firstChild);

			}
			else {

				document.querySelector('h1').appendChild(div.firstChild);

			}

			SB.initIndexButton();

        },
		addReindexButton: ()=> {

			if(document.querySelector('#menu-media')) {

				let li = document.createElement('li');
				let a = document.createElement('a');
				a.textContent = 'Reindex Media';
				a.classList.add('ss88_reindex');
				a.href = '#';
				a.setAttribute('data-orig', 'Reindex Media');
				li.appendChild(a);
				
				document.querySelector('#menu-media>ul').appendChild(li);

				if(!document.querySelector('#menu-media a[href*="page=ss88-mlfs-analyze"]')) {

					let li_analyze = document.createElement('li');
					let a_analyze = document.createElement('a');
					a_analyze.textContent = 'Analyze';
					a_analyze.classList.add('ss88_analyze');
					a_analyze.href = 'upload.php?page=ss88-mlfs-analyze';
					li_analyze.appendChild(a_analyze);
					document.querySelector('#menu-media>ul').appendChild(li_analyze);

				}

				SB.initIndexButton('.ss88_reindex');

			}

		},
		addSize: (d, t) => {

			var tooltip = (t) ? '<span class="tooltiptext">'+ t +'</span>' : '';
			var div = document.querySelector('.ss88sizeinfo');
			var div_inner = '('+ d +')'+ tooltip;

			if(div) {

				div.innerHTML = div_inner;

			} else {

				div = document.createElement('div');
				div.innerHTML = '<span class="ss88sizeinfo">'+ div_inner +'</span>';

				if(document.querySelector('h1')) {

					document.querySelector('h1').appendChild(div.firstChild);
	
				}
				else {
	
					document.querySelector('h2').appendChild(div.firstChild);
	
				}

			}

		},
		sendAlert: (type, text) => {

			new Noty({ 
				type: type,
				timeout: 4500,
				layout: 'bottomRight',
				theme: 'metroui',
				text: text
			}).show();

		},
		initIndexButton: (which = '.ss88indexmedia')=>{

			var button = document.querySelector(which);
			var reindex = (which == '.ss88_reindex') ? true : false;

			button.addEventListener('click', (e) => {

				e.preventDefault();
				SB.buttonLoading(button, true);
					
					fetch(SS88_MLFS_Data.ajax_url, {

						method: 'POST',
						headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
						body: new URLSearchParams(requestData = { action: "SS88MLFS_index", reindex: reindex, nonce: SS88_MLFS_Data.nonce }).toString(),
				
				}).then(function(response) {

					return response.json();

				}).then(function(response) {

					if(response.success) {

						SB.sendAlert('success', response.data.message)
						SB.outputIndex(response.data.html)
						SB.indexCheck()

					}
					else {

						var errorText = 'Unknown error.';
						if(response && response.data) {

							if(response.data.httpcode && response.data.body) {

								errorText = 'Error ' + response.data.httpcode +': ' + response.data.body;

							}
							else if(response.data.error) {

								errorText = response.data.error;

							}
							else if(response.data.body) {

								errorText = response.data.body;

							}
							else if(response.data.message) {

								errorText = response.data.message;

							}

						}

						SB.sendAlert('error', errorText);

					}

					SB.buttonLoading(button, false);

				}).catch( err => { console.log(err); SB.sendAlert('error', err.message); } );

			});

		},
		buttonLoading: (element, tf) => {

			if(tf) {
				
				element.innerHTML = '<div class="ss88mlfs-lds-ellipsis"><div></div><div></div><div></div><div></div></div>';
				element.setAttribute('disabled', true);

			}
			else {
				
				element.innerHTML = element.dataset.orig;
				element.removeAttribute('disabled');

			}

		},
		outputIndex: (data) => {

			data.forEach(post => { 

				var tr = document.querySelector('tr#post-' + post.attachment_id);

				if(tr) {

					let tdContent = tr.querySelector('.SS88_MediaLibraryFileSize');
					if(tdContent) tdContent.innerHTML = post.html;

				}
			
			});

		},
		initVariantsModal: () => {

			document.querySelectorAll('.ss88MLFS_VV').forEach(button => { 

				button.addEventListener('click', (e) => {

					e.preventDefault();

					let attachment_id = button.dataset.aid;
					let attachment_data = ss88MLFS_VV[attachment_id];
					if(!attachment_id || !attachment_data) return;
						
					let overlay = document.createElement('div'); overlay.classList.add('ss88MLFS_VV_overlay');
					let modal = document.createElement('div'); modal.classList.add('ss88MLFS_VV_modal');

					overlay.addEventListener('click', (e) => {

						if(e.target.classList.contains('ss88MLFS_VV_overlay')) overlay.remove();

					});

					overlay.appendChild(modal); document.body.appendChild(overlay);

					attachment_data.sort((a, b) => a.width - b.width);

					attachment_data.forEach(function(data, index) {

						let box = document.createElement('div'); box.classList.add('ss88MLFS_VV_box');

						let span_img = document.createElement('span'); span_img.classList.add('img');
						span_img.appendChild(document.createTextNode(data.width));
						span_img.appendChild(document.createElement('br'));
						span_img.appendChild(document.createTextNode('x'));
						span_img.appendChild(document.createElement('br'));
						span_img.appendChild(document.createTextNode(data.height));

						let link = document.createElement('a');
						link.textContent = 'Click to View Image';
						link.target = '_blank';
						link.rel = 'noopener noreferrer';
						try {

							let url = new URL(data.filename, window.location.origin);
							link.href = (url.protocol === 'http:' || url.protocol === 'https:') ? url.href : '#';

						} catch(e) {

							link.href = '#';

						}
						span_img.appendChild(link);

						let span_name = document.createElement('span'); span_name.classList.add('name');
						span_name.textContent = (data.filename || '').split(/[\\/]/).pop();

						let span_size = document.createElement('span'); span_size.classList.add('size');
						span_size.textContent = 'Filesize: ' + data.filesize_hr;

						let span_name2 = document.createElement('span'); span_name2.classList.add('name2');
						span_name2.textContent = 'Name: ' + data.size;

						box.appendChild(span_img);
						box.appendChild(span_name);
						box.appendChild(span_size);
						box.appendChild(span_name2);
						modal.appendChild(box);

					})
	
				});
			
			});

		},
		initGridModal: ()=> {

			//if(!document.querySelector('#wp-media-grid')) return;

			SB.gridVariantsCache = {};
			SB.gridVariantsPending = {};

			let observer = new MutationObserver(function() {

				SB.gridVariantsRender();

			});

			observer.observe(document.body, {
				childList: true,
				subtree: true,
				attributes: true,
				attributeFilter: ['class']
			});

			document.body.addEventListener('click', function() {

				setTimeout(function() {

					SB.gridVariantsRender();

				}, 120);

			});

		},
			gridVariantsRender: ()=> {

				let modal = document.querySelector('.media-modal');
				if(!modal) return;

				let selected = modal.querySelector('.attachments .attachment.selected');
				let details = modal.querySelector('.attachment-details .details');
				if(!details) return;

				let attachment_id = '';
				if(selected) attachment_id = selected.getAttribute('data-id');
				if(!attachment_id) {

					let detailsLink = modal.querySelector('.attachment-info .actions a[href*="post.php?post="]');
					if(detailsLink && detailsLink.href) {

						try {

							let detailsUrl = new URL(detailsLink.href, window.location.origin);
							attachment_id = detailsUrl.searchParams.get('post');

						} catch(e) {

							let detailsMatch = detailsLink.href.match(/[?&]post=(\d+)/);
							if(detailsMatch) attachment_id = detailsMatch[1];

						}

					}

				}
				if(!attachment_id) return;

				let existing = details.querySelector('.ss88MLFS_grid_variants');
				if(existing && existing.getAttribute('data-aid')==attachment_id) return;
				if(existing) existing.remove();

				if(SB.gridVariantsCache[attachment_id]) {

					SB.gridVariantsOutput(details, attachment_id, SB.gridVariantsCache[attachment_id]);
					return;

				}
				if(SB.gridVariantsPending[attachment_id]) return;

				SB.gridVariantsPending[attachment_id] = true;

				fetch(SS88_MLFS_Data.ajax_url + '?' + new URLSearchParams({ action: 'SS88MLFS_attachmentDetails', attachment_id: attachment_id, nonce: SS88_MLFS_Data.nonce })).then(function(response) {

				return response.json();

			}).then(function(response) {

				if(!response || !response.success || !response.data) return;
				SB.gridVariantsCache[attachment_id] = response.data;
				SB.gridVariantsOutput(details, attachment_id, response.data);

			}).catch( err => { console.log(err); } ).finally(function() {

				SB.gridVariantsPending[attachment_id] = false;

			});

		},
		gridVariantsOutput: (details, attachment_id, data)=> {

			if(!details || !data) return;
			let variants = Array.isArray(data.variants) ? data.variants : [];
			let variantBytes = parseInt(data.variant_size_bytes || 0, 10);
			if(variantBytes<=0 || variants.length==0) return;

			let wrap = document.createElement('div'); wrap.classList.add('misc-pub-section', 'ss88MLFS_grid_variants');
			wrap.setAttribute('data-aid', attachment_id);

			let title = document.createElement('span'); title.classList.add('ss88MLFS_grid_variants_title');

			let titleStrong = document.createElement('strong');
			titleStrong.textContent = 'Variants size:';
			title.appendChild(titleStrong);
			title.appendChild(document.createTextNode(' ' + (data.variant_size || '0 B')));
			wrap.appendChild(title);

			if(variants.length>0) {

				let list = document.createElement('ul'); list.classList.add('ss88MLFS_grid_variants_list');

				variants.forEach(function(variant) {

					let item = document.createElement('li');
					let line = document.createElement('span');
					line.textContent = (variant.size || 'variant') + ' (' + (variant.width || 0) + 'x' + (variant.height || 0) + ') - ' + (variant.filesize_hr || 'Unknown');
					item.appendChild(line);

					if(variant.filename) {

						let link = document.createElement('a');
						link.textContent = 'View';
						link.target = '_blank';
						link.rel = 'noopener noreferrer';
						try {

							let url = new URL(variant.filename, window.location.origin);
							link.href = (url.protocol === 'http:' || url.protocol === 'https:') ? url.href : '#';

						} catch(e) {

							link.href = '#';

						}

						item.appendChild(document.createTextNode(' '));
						item.appendChild(link);

					}

					list.appendChild(item);

				});

				wrap.appendChild(list);

			}

			let dimensions = details.querySelector('.dimensions');
			let fileSize = details.querySelector('.file-size');
			let fileType = details.querySelector('.file-type');
			if(dimensions) dimensions.insertAdjacentElement('afterend', wrap);
			else if(fileSize) fileSize.insertAdjacentElement('afterend', wrap);
			else if(fileType) fileType.insertAdjacentElement('afterend', wrap);
			else details.appendChild(wrap);

		}

	}

	let SB = SS88_MediaLibraryFileSize;
	SB.init()

}

window.addEventListener('DOMContentLoaded', (event) => {

	if(document.querySelector('.wp-list-table.media') || document.querySelector('#wp-media-grid')) {
		
		SS88_MediaLibraryFileSize_init_MediaLibrary();

	}

});
