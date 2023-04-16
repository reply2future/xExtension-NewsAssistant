(function () {
	const summaryIdsElement = document.getElementById('summary_ids');
	if (!summaryIdsElement) return;

	const summaryIds = JSON.parse(summaryIdsElement.innerHTML);
	summaryIdsElement.outerHTML = '';

	const setReadBtn = document.getElementById('set_read_btn');
	const loader = document.getElementById('loader');
	const readBadge = document.getElementById('read_badge');

	readBadge.innerText = (summaryIds && summaryIds.length) || 0;

	function showLoading() {
		loader.style.display = 'flex';
	}

	function hideLoading() {
		loader.style.display = 'none';
	}

	setReadBtn.onclick = function (ev) {
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

			window.location.href = '/';
		};
		req.setRequestHeader('Content-Type', 'application/json');
		req.send(JSON.stringify({
			_csrf: context.csrf,
			id: summaryIds,
			ajax: 1,
		}));
	}
}());