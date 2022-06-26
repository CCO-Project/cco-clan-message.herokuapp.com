<!DOCTYPE html>
<html lang="zh-TW" style="font-size: 18px;">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clan Messages</title>
    <link rel="stylesheet" href="./css/main.css">
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Ubuntu">
</head>

<body style="background-color: #1e1e1e; color: #c0e4dc; align-items: center">
    <div id="container">
        <h2>既然都攤在陽光下了，與其被少數人利用，不如讓大家都能使用。</h2>
        <div class="span right" style="opacity: 0.5; font-size: smaller;">最後更新於 2022.06.26 10:50:19 - 修正標記錯誤、微調介面</div>

        <table>
            <colgroup>
                <col style="width: calc(200% / 4)">
                <col style="width: calc(100% / 4)">
                <col style="width: calc(100% / 4)">
            </colgroup>
            <thead>
                <tr>
                    <th>公會標籤（三個字元的那個，不分大小寫）</th>
                    <th>歷史記錄筆數 (32~1500)</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><input type="text" id="c"></td>
                    <td><input type="number" id="n" value="1500" min="32" max="1500"></td>
                    <td><input type="button" value="搜尋"></td>
                </tr>
            </tbody>
        </table>

        <br>

        <div>結果 (共 <span id="number">x</span> 筆)</div>

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
            const n = document.querySelector("#n");

            button.onclick = fetchData;
            c.onkeydown = e => e.keyCode == 13 ? fetchData() : true;
            n.onkeydown = e => e.keyCode == 13 ? fetchData() : true;
            changeListHeight();
            window.addEventListener("resize", changeListHeight);

            async function fetchData() {
                const fd = new FormData();
                fd.append("c", c.value);
                fd.append("n", n.value);

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
                } else {
                    result.innerHTML = `<p style="color: red">Error: ${response.message}</p>`;
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
        })();
    </script>
</body>

</html>