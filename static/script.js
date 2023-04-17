(function () {
	const loader = document.getElementById('loader');

	const summaryContentDiv = document.getElementById('summary_content');

	const queryString = window.location.search;
	const urlParams = new URLSearchParams(queryString);
	const evtSource = new EventSource(`/i/?c=assistant&a=stream&cat_id=${urlParams.get('cat_id')}&state=${urlParams.get('state')}`);
	
	evtSource.onmessage = (event) => {
		console.log('Received message');

		summaryContentDiv.innerText += event.data;
	};

	evtSource.onopen = (event) => {
		console.log('Connected the server');
	};

	evtSource.onerror = (err) => {
		console.error("EventSource failed:", err);

		evtSource.close();
	};

	evtSource.addEventListener('done', () => {
		console.log('Task done!');

		evtSource.close();
	});

	evtSource.addEventListener('empty', (event) => {
		console.log('There is no news!');

		summaryContentDiv.innerText = event.data;
		evtSource.close();
	});

	evtSource.addEventListener('load_summary_ids', (event) => {
		console.log('Load summary ids');

		const setReadBtn = document.getElementById('set_read_btn');
		const readBadge = document.getElementById('read_badge');
		const summaryIds = JSON.parse(event.data).summary_ids;

		if (summaryIds.length > 0) setReadBtn.onclick = function (ev) {
			ev.stopPropagation();

			showLoading();

			const req = new XMLHttpRequest();
			req.open('POST', './?c=entry&a=read', true);
			req.responseType = 'json';
			req.onerror = function (e) {
				hideLoading();

				badAjax(this.status == 403);
			};
			req.onload = function (e) {
				if (this.status != 200) {
					return req.onerror(e);
				}

				hideLoading();

				window.location.href = `/i/?a=normal&get=c_${urlParams.get('cat_id')}`;
			};
			req.setRequestHeader('Content-Type', 'application/json');
			req.send(JSON.stringify({
				_csrf: context.csrf,
				id: summaryIds,
				ajax: 1,
			}));
		}

		readBadge.innerText = (summaryIds && summaryIds.length) || 0;
	});


	function showLoading() {
		loader.style.display = 'flex';
	}

	function hideLoading() {
		loader.style.display = 'none';
	}

	const backBtn = document.getElementById('back_btn');
	backBtn.onclick = () => {
		history.back();
	}
}());