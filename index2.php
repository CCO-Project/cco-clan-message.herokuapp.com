<!DOCTYPE html>
<html lang="zh-TW" style="font-size: 16px;">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clan Messages</title>
    <link rel="stylesheet" href="./css/main.css?<?=$_SERVER['REQUEST_TIME']?>">
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Ubuntu">
</head>

<body style="background-color: #1e1e1e; color: #c0e4dc; align-items: center">
    <div id="container">
        <div class="header">既然都攤在陽光下了，與其被少數人利用，不如讓大家都能使用。</div>
        <div class="span right update-message">最後更新於 2022.06.26 13:17:10 - 微調手機介面、調整搜尋結果（直接搜全部）</div>

        <table>
            <colgroup>
                <col style="width: calc(200% / 3)">
                <col style="width: calc(100% / 3)">
            </colgroup>
            <thead>
                <tr>
                    <th colspan="2">公會標籤（三個字元的那個，不分大小寫）</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><input type="text" id="c" style="font-size: 1.5rem; width: calc(100% - 8px);"></td>
                    <td><input type="button" value="搜尋" style="font-size: 1.5rem;"></td>
                </tr>
            </tbody>
        </table>

        <br>

        <div>
            結果 (共 <span id="number">x</span> 筆)
            <span id="status"></span>
        </div>

        <div class="chat-list" id="result">

        </div>
    </div>

    <script>
        (() => {
            const MAIN_THEME = {
                background: "#1e1e1e",
                text: "#c0e4dc"
            };

            // 從客戶端摳下來的，原本就長這副德行
            const COLOR_TIER = {
                donation1: "rgb(192, 228, 220)",
                donation2: "rgb(79, 189, 116)",
                donation3: "#ccc541",
                donation4: "#8A2BE2",
                donation5: "#d07f3c",
                donation6: "rgb( 34, 197, 238)",
                donation7: "rgb(233, 87, 243)",
                MODERATOR: "#FF69B4"
            };

            const result = document.querySelector("#result");
            const button = document.querySelector("input[type='button']");
            const number = document.querySelector("#number");
            const c = document.querySelector("#c");
            let statusId;

            button.onclick = fetchData;
            c.onkeydown = e => e.keyCode == 13 ? fetchData() : true;
            changeListHeight();
            window.addEventListener("resize", changeListHeight);

            async function fetchData() {
                const fd = new FormData();
                fd.append("c", c.value);
                
                showStatus(1);
                const response = await fetch("./api/cm.php", {
                    method: "POST",
                    body: fd
                }).then(
                    r => r.json()
                );

                if (response.success === true) {
                    result.innerHTML = "";
                    let ta = document.createElement("textarea");
                    ta.style.cssText = "width: 90%; height: 600px";
                    ta.readOnly = true;

                    response.data.reverse();

                    response.data.forEach(e => {
                        result.insertAdjacentElement("beforeend", singleChat(e));
                    });

                    number.innerHTML = response.data.length;
                    showStatus(0);
                } else {
                    number.innerHTML = "x";
                    result.innerHTML = `<p style="color: red">Error: ${response.message}</p>`;
                    showStatus(2);
                }
            }

            function hydrate(e) {
                const s = "-0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ_abcdefghijklmnopqrstuvwxyz";
                let t = 0;

                e = e.split(" ")[0];
                if (e)
                    for (let n = e.length, r = 0; r < n; r++)
                        t += s.indexOf(e[r]) * Math.pow(s.length, n - r - 1);

                let d = new Date(t);
                d.setTime(d.getTime() + 8 * 60 * 60 * 1000);

                return {
                    date: d,
                    str: d.toISOString().replace("T", " ").replace(/\..+/, "")
                };
            }

            function translate(e) {
                const resultTags = [];
                let m = e.message;

                let tags2 = m.match(/\$!\{playerName:.+?\}/g);
                if (tags2 !== null) {
                    tags2.forEach(tag => {
                        let t = decodeURIComponent(tag);
                        t = t.replace(/\$\!\{playerName:(.+?)\}/g, "$1");
                        m = m.replace(tag, `(((${resultTags.length})))`);

                        resultTags[resultTags.length] = `<div class="tag" data-tier="none">${t}</div>`;
                    });
                }
                let tags = m.match(/\$!\{player.+?\}/);
                if (tags !== null) {
                    tags.forEach(tag => {
                        let t = decodeURIComponent(tag);
                        t = JSON.parse(t.replace("$!{player:", "").replace(/\}$/, ""));
                        m = m.replace(tag, `(((${resultTags.length})))`);

                        resultTags[resultTags.length] = `<div class="tag" data-tier="${t.t}">${t.n}</div>`;
                    });
                }

                return {
                    msg: escapeHtml(m),
                    tags: resultTags
                };
            }

            function singleChat(m) {
                const username = m.senderName;
                const time = `${hydrate(m.time).str} (UTC+8)`;
                const clan = `[${m.clanShortName.toUpperCase()}]`;
                const level = `LEVEL ${m.lv}`;
                const message = translate(m);
                const color = COLOR_TIER[m.effectName] ? COLOR_TIER[m.effectName] : "#c0e4dc";

                const eChat = document.createElement("div");
                const eChatTop = document.createElement("div");
                const eChatTopLeft = document.createElement("div");
                const eChatTopRight = document.createElement("div");
                const eChatBody = document.createElement("div");
                const eChatFooter = document.createElement("div");
                const eChatFooterLeft = document.createElement("div");
                const eChatFooterRight = document.createElement("div");

                // style overwrite
                eChatTop.style.backgroundColor = color;
                eChatBody.style.borderColor = color;

                // class
                eChat.classList.add("chat");
                eChatTop.classList.add("chat-top");
                eChatTopLeft.classList.add("span", "left");
                eChatTopRight.classList.add("span", "right");
                eChatBody.classList.add("chat-body");
                eChatFooter.classList.add("chat-footer");
                eChatFooterLeft.classList.add("span", "left");
                eChatFooterRight.classList.add("span", "right");

                // data
                message.tags.forEach((tag, index) => {
                    message.msg = message.msg.replace(`(((${index})))`, tag);
                });
                eChatTopLeft.innerText = username;
                eChatTopRight.innerText = clan;
                eChatBody.innerHTML = message.msg;
                eChatFooterLeft.innerText = level;
                eChatFooterRight.innerText = time;

                // structure
                eChat.append(eChatTop, eChatBody, eChatFooter);
                eChatTop.append(eChatTopLeft, eChatTopRight);
                eChatFooter.append(eChatFooterLeft, eChatFooterRight);

                return eChat;
            }

            function escapeHtml(unsafe) {
                return unsafe
                    .replace(/&/g, "&amp;")
                    .replace(/</g, "&lt;")
                    .replace(/>/g, "&gt;")
                    .replace(/"/g, "&quot;")
                    .replace(/'/g, "&#039;");
            }

            function changeListHeight() {
                const calcHeightPx = window.innerHeight - result.getClientRects()[0].y - 10;
                result.style.minHeight = `${calcHeightPx}px`;
                result.style.height = `${calcHeightPx}px`;
            }

            function showStatus(type = 0) {
                const status = document.querySelector("#status");
                const COLOR_STATUS = {
                    success: "#9bfc9b",
                    processing: "#fffd77",
                    error: "#f00"
                };

                if (type == 0) {
                    status.innerText = "已完成";
                    status.style.color = COLOR_STATUS.success;
                    clearInterval(statusId);
                } else if (type == 1) {
                    let t = 0;
                    let timer = document.createElement("span");
                    status.innerText = "搜尋中... ";
                    status.style.color = COLOR_STATUS.processing;
                    status.insertAdjacentElement("beforeend", timer);

                    statusId = setInterval(() => {
                        t += 0.033;
                        timer.innerText = `${t.toFixed(2)}s`;
                    }, 33);
                } else if (type == 2) {
                    status.innerText = "錯誤";
                    status.style.color = COLOR_STATUS.error;
                    clearInterval(statusId);
                }
            }
        })();
    </script>
</body>

</html>