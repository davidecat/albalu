let SS88_MLFS_Analyze = {};

function SS88_MLFS_Analyze_init() {

	SS88_MLFS_Analyze = {

		init: ()=> {

			SA.wrap = document.querySelector('#ss88-mlfs-analyze-page');
			if(!SA.wrap) return;

			SA.chart = SA.wrap.querySelector('#ss88-analyze-pie');
			SA.empty = SA.wrap.querySelector('#ss88-analyze-empty');
			SA.keyBody = SA.wrap.querySelector('#ss88-analyze-key-body');
			SA.listBody = SA.wrap.querySelector('#ss88-analyze-list-body');
			SA.listTitle = SA.wrap.querySelector('#ss88-analyze-list-title');
			SA.summaryTitle = SA.wrap.querySelector('#ss88-analyze-summary-title');
			SA.loadMoreBtn = SA.wrap.querySelector('#ss88-analyze-load-more');
			SA.categories = [];
			SA.activeCategory = 'all';
			SA.activeLabel = 'All Files';
			SA.listOffset = 0;
			SA.hasMore = false;
			SA.chartHasAnimated = false;

			SA.loadMoreBtn.addEventListener('click', function() {

				SA.loadList(SA.activeCategory, SA.activeLabel, false);

			});

			SA.loadSummary();

		},
		loadSummary: ()=> {

			SA.fetchData({ action: 'SS88MLFS_analyzeSummary', nonce: ss88Analyze.nonce }).then(function(response) {

					if(!response || !response.success || !response.data) return;
					SA.categories = Array.isArray(response.data.categories) ? response.data.categories : [];
					SA.summaryTitle.textContent = 'Summary (' + (response.data.total_size_hr || '0 B') + ')';
						SA.renderKey();
						SA.renderChart();

					if(SA.categories.length>0) {

						if(SA.activeCategory=='all') SA.selectCategory('all', 'All Files');
						else {

							let selected = SA.categories.find(function(category) {

								return category.slug == SA.activeCategory;

							});
							if(!selected) SA.selectCategory('all', 'All Files');
							else SA.selectCategory(selected.slug, selected.label);

						}

					}
					else {

					SA.listBody.innerHTML = '<tr><td colspan="5">No indexed media found. Please run Index Media first.</td></tr>';
					SA.listTitle.textContent = 'No category selected';

				}

			}).catch( err => { console.log(err); } );

		},
		renderKey: ()=> {

			SA.keyBody.innerHTML = '';

			if(SA.categories.length==0) {

				SA.keyBody.innerHTML = '<tr><td colspan="3">No data available.</td></tr>';
				return;

			}

			SA.categories.forEach(function(category) {

				let tr = document.createElement('tr');
				tr.setAttribute('data-category', category.slug);
				tr.style.cursor = 'pointer';
				tr.addEventListener('click', function() {

					SA.selectCategory(category.slug, category.label);

				});

				let tdType = document.createElement('td');
				let dot = document.createElement('span'); dot.classList.add('ss88-analyze-dot');
				dot.style.backgroundColor = category.color;
				tdType.appendChild(dot);
				tdType.appendChild(document.createTextNode(category.label));

				let tdCount = document.createElement('td');
				tdCount.textContent = Number(category.count || 0).toLocaleString();

				let tdSize = document.createElement('td');
				tdSize.textContent = category.size_hr || '0 B';

				tr.appendChild(tdType);
				tr.appendChild(tdCount);
				tr.appendChild(tdSize);
				SA.keyBody.appendChild(tr);

			});

		},
			renderChart: ()=> {

			SA.chart.innerHTML = '';

			let total = 0;
			SA.categories.forEach(function(category) {

				total += parseInt(category.size || 0, 10);

			});

			if(total<=0) {

				SA.empty.classList.add('show');
				return;

			}

			SA.empty.classList.remove('show');

			if(SA.categories.length==1) {

				let category = SA.categories[0];
				let circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
				circle.setAttribute('cx', '120');
				circle.setAttribute('cy', '120');
				circle.setAttribute('r', '96');
				circle.setAttribute('fill', category.color);
				circle.classList.add('ss88-analyze-slice');
				circle.setAttribute('data-category', category.slug);
				circle.addEventListener('click', function() {

					SA.selectCategory(category.slug, category.label);

				});

				let title = document.createElementNS('http://www.w3.org/2000/svg', 'title');
				title.textContent = category.label + ' - ' + category.size_hr;
				circle.appendChild(title);
				if(!SA.chartHasAnimated) {

					circle.classList.add('ss88-analyze-slice-draw');
					circle.style.animationDelay = '0ms';

				}
				SA.chart.appendChild(circle);
				SA.chartHasAnimated = true;
				return;

			}

			let slices = SA.categories.filter(function(category) {

				return parseInt(category.size || 0, 10)>0;

			}).map(function(category) {

				return {
					category: category,
					rawAngle: (parseInt(category.size || 0, 10) / total) * 360,
					angle: 0
				};

			});

			let minArcPx = 10;
			let minAngle = (minArcPx * 180) / (Math.PI * 96);
			if((minAngle * slices.length)>360) minAngle = 360 / slices.length;

			slices.forEach(function(slice) {

				slice.angle = (slice.rawAngle<minAngle) ? minAngle : slice.rawAngle;

			});

			let angleTotal = 0;
			slices.forEach(function(slice) { angleTotal += slice.angle; });
			let overBy = angleTotal - 360;

			if(overBy>0) {

				let reducible = slices.filter(function(slice) {

					return slice.rawAngle>minAngle;

				});

				let reducibleTotal = 0;
				reducible.forEach(function(slice) {

					reducibleTotal += (slice.angle - minAngle);

				});

				if(reducibleTotal>0) {

					reducible.forEach(function(slice) {

						let room = slice.angle - minAngle;
						let reduceBy = overBy * (room / reducibleTotal);
						slice.angle = Math.max(minAngle, slice.angle - reduceBy);

					});

				}

			}

			let startAngle = 0;
			slices.forEach(function(slice) {

				let category = slice.category;
				let endAngle = startAngle + slice.angle;

				let path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
				path.setAttribute('d', SA.piePath(120, 120, 96, startAngle, endAngle));
				path.setAttribute('fill', category.color);
				path.classList.add('ss88-analyze-slice');
				path.setAttribute('data-category', category.slug);
				path.addEventListener('click', function() {

					SA.selectCategory(category.slug, category.label);

				});

				let title = document.createElementNS('http://www.w3.org/2000/svg', 'title');
				title.textContent = category.label + ' - ' + category.size_hr;
				path.appendChild(title);
				if(!SA.chartHasAnimated) {

					path.classList.add('ss88-analyze-slice-draw');
					path.style.animationDelay = (slices.indexOf(slice) * 140) + 'ms';

				}

				SA.chart.appendChild(path);
				startAngle = endAngle;

			});

			SA.chartHasAnimated = true;

		},
		piePath: (cx, cy, r, startAngle, endAngle)=> {

			let start = SA.polarToCartesian(cx, cy, r, endAngle);
			let end = SA.polarToCartesian(cx, cy, r, startAngle);
			let largeArc = endAngle - startAngle <= 180 ? '0' : '1';

			return [
				'M', cx, cy,
				'L', start.x, start.y,
				'A', r, r, 0, largeArc, 0, end.x, end.y,
				'Z'
			].join(' ');

		},
		polarToCartesian: (cx, cy, r, angle)=> {

			let rad = (angle - 90) * Math.PI / 180;
			return {
				x: cx + (r * Math.cos(rad)),
				y: cy + (r * Math.sin(rad))
			};

		},
			selectCategory: (slug, label)=> {

				SA.activeCategory = slug;
				SA.activeLabel = label;

				SA.wrap.querySelectorAll('#ss88-analyze-pie .ss88-analyze-slice').forEach(function(slice) {

					if(slug=='all') {

						slice.classList.remove('is-active');
						slice.classList.remove('is-muted');

					}
					else {

						let active = slice.getAttribute('data-category') == slug;
						slice.classList.toggle('is-active', active);
						slice.classList.toggle('is-muted', !active);

					}

				});

				SA.wrap.querySelectorAll('#ss88-analyze-key-body tr').forEach(function(tr) {

					tr.classList.toggle('is-active', slug!='all' && tr.getAttribute('data-category') == slug);

				});

				SA.loadList(slug, label, true);

			},
			loadList: (slug, label, reset = false)=> {

				let offset = reset ? 0 : SA.listOffset;
				let limit = reset ? 25 : 50;

				SA.listTitle.textContent = label + ' (loading...)';
				if(reset) SA.listBody.innerHTML = '<tr><td colspan="5">Loading...</td></tr>';
				SA.loadMoreBtn.disabled = true;

				SA.fetchData({ action: 'SS88MLFS_analyzeList', category: slug, offset: offset, limit: limit, nonce: ss88Analyze.nonce }).then(function(response) {

					if(!response || !response.success || !response.data) {

						SA.loadMoreBtn.parentElement.style.display = 'none';
						return;

					}
					SA.listTitle.textContent = response.data.label + ' - Largest Files';
					SA.renderList(response.data.rows || [], !reset);
					SA.listOffset = parseInt(response.data.next_offset || 0, 10);
					SA.hasMore = !!response.data.has_more;
					SA.loadMoreBtn.parentElement.style.display = SA.hasMore ? 'block' : 'none';

				}).catch( err => { console.log(err); } ).finally(function() {

					SA.loadMoreBtn.disabled = false;

				});

			},
			renderList: (rows, append = false)=> {

				if(!append) SA.listBody.innerHTML = '';

				if((!rows || rows.length==0) && !append) {

					SA.listBody.innerHTML = '<tr><td colspan="5">No files were found in this category.</td></tr>';
					return;

			}

			rows.forEach(function(row) {

				let tr = document.createElement('tr');
				tr.setAttribute('data-id', row.id);

				let tdThumb = document.createElement('td');
				let img = document.createElement('img');
				img.src = row.thumbnail;
				img.alt = row.name || '';
				img.loading = 'lazy';
				img.classList.add('ss88-analyze-thumb');
				tdThumb.appendChild(img);

				let tdName = document.createElement('td');
				tdName.textContent = row.name || '';

				let tdUploadedTo = document.createElement('td');
				tdUploadedTo.textContent = row.uploaded_to || '(Unattached)';

				let tdSize = document.createElement('td');
				tdSize.textContent = row.size_hr || '0 B';

				let tdActions = document.createElement('td');
				tdActions.classList.add('ss88-analyze-actions');

				let viewLink = document.createElement('a');
				viewLink.href = row.view_url || '#';
				viewLink.target = '_blank';
				viewLink.rel = 'noopener noreferrer';
				viewLink.textContent = 'View';
				tdActions.appendChild(viewLink);

				let editLink = document.createElement('a');
				editLink.href = row.edit_url || '#';
				editLink.textContent = 'Edit';
				editLink.target = '_blank';
				editLink.rel = 'noopener noreferrer';
				tdActions.appendChild(editLink);

				if(row.can_delete) {

					let delBtn = document.createElement('button');
					delBtn.type = 'button';
					delBtn.textContent = 'Delete';
					delBtn.classList.add('button-link-delete');
					delBtn.addEventListener('click', function() {

						SA.deleteAttachment(row.id, tr);

					});
					tdActions.appendChild(delBtn);

				}

				tr.appendChild(tdThumb);
				tr.appendChild(tdName);
				tr.appendChild(tdUploadedTo);
				tr.appendChild(tdSize);
				tr.appendChild(tdActions);
				SA.listBody.appendChild(tr);

			});

		},
		deleteAttachment: (attachment_id, tr)=> {

			if(!confirm('Delete this attachment permanently?')) return;

			fetch(ss88Analyze.ajax_url, {
				method: 'POST',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
				body: new URLSearchParams({ action: 'SS88MLFS_analyzeDelete', nonce: ss88Analyze.nonce, attachment_id: attachment_id }).toString()
			}).then(function(response) {

				return response.json();

			}).then(function(response) {

				if(!response || !response.success) return;
				if(tr) tr.remove();
				SA.loadSummary();

			}).catch( err => { console.log(err); } );

		},
		fetchData: (params)=> {

			return fetch(ss88Analyze.ajax_url + '?' + new URLSearchParams(params)).then(function(response) {

				return response.json();

			});

		}

	}

	let SA = SS88_MLFS_Analyze;
	SA.init();

}

window.addEventListener('DOMContentLoaded', function() {

	SS88_MLFS_Analyze_init();

});
