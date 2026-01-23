let SS88_MediaLibraryFileSize = {};

function SS88_MediaLibraryFileSize_init_MediaLibrary() {

	SS88_MediaLibraryFileSize = {

		init: ()=>{

            SB.indexCheck();
			SB.addReindexButton();
			SB.initVariantsModal();
			
		},
		indexCheck: ()=> {
			
			fetch(ss88.ajax_url + '?' + new URLSearchParams({ action: 'SS88MLFS_indexCount' })).then(function(response) {

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
					
				fetch(ss88.ajax_url, {

					method: 'POST',
					headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
					body: new URLSearchParams(requestData = { action: "SS88MLFS_index", reindex: reindex }).toString(),
				
				}).then(function(response) {

					return response.json();

				}).then(function(response) {

					if(response.success) {

						SB.sendAlert('success', response.data.message)
						SB.outputIndex(response.data.html)
						SB.indexCheck()

					}
					else {

						SB.sendAlert('error', 'Error ' + response.data.httpcode +': ' + response.data.body);

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

						document.querySelector('.ss88MLFS_VV_modal').innerHTML += `
						
							<div class="ss88MLFS_VV_box">
								<span class="img">
									${data.width}
									<br>x<br>
									${data.height}
									<a href="${data.filename}" target="_blank">Click to View Image</a>
								</span>
								<span class="name">${data.filename.split(/[\\/]/).pop()}</span>
								<span class="size">Filesize: ${data.filesize_hr}</span>
								<span class="name2">Name: ${data.size}</span>
							</div>
						
						`;

					})
	
				});
			
			});

		}

	}

	let SB = SS88_MediaLibraryFileSize;
	SB.init()

}

window.addEventListener('DOMContentLoaded', (event) => {

	if(document.querySelector('.wp-list-table.media')) SS88_MediaLibraryFileSize_init_MediaLibrary();

});