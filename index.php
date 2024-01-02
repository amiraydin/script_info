<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Language" content="en">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" integrity="sha512-MV7K8+y+gLIBoVD59lQIYicR65iaqukzvf/nwasF0nqhPay5w/9lJmVM2hMDcnK1OnMGCdVK+iQrJ7lzPJQd1w==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="chemin.css">
    <title>Script Informations on Directory </title>
</head>
<?php
header('Content-Language: en');
include('./chemin_info.php');
?>

<body>
    <main class="m-2 rounded">
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="d-flex align-items-center">
                <img src="./logo.png" style="cursor: pointer;" class="img-fluid rounded-circle" width="100" height="100" alt="logo" onclick="return(window.location.reload())" />
                <h5 class="ms-4 text-center"> Informations on your Directory</h5>
            </div>
            <div class="container fluid">
                <div class="collapse justify-content-center navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav">
                        <li class="nav-item me-4">
                            <label for="filepicker">Search your directory</label>
                            <input type="file" name="chemin" webkitdirectory multiple id="filepicker" required class="form-control form-control-sm" />
                        </li>
                        <li class="nav-item ms-4">
                            <label for="manually">Enter the path to your directory</label>
                            <form class="d-flex gap-2" action="" method="post">
                                <input type="text" name="chemin" id="manually" autocomplete="on" required class="form-control col-8 form-control-sm" />
                                <button class="btn btn-outline-info btn-sm" type="submit">Manually</button>
                            </form>
                        </li>
                        <!-- <li class="nav-item ms-5 me-3"> -->
                        <!-- <label for="searchInput">Search the name of your folder</label>
                            <input class="form-control bg-gradient form-control-sm" id="searchInput" onkeyup="searchFunc()" type="search" autocomplete="off" placeholder="Search" aria-label="Search"> -->
                        <!-- <label for="searchInput"><span class="sicon_list"><i class="fa-solid fa-magnifying-glass"></i></span></label> -->
                        <!-- </li> -->
                    </ul>
                </div>
            </div>
        </nav>
        <div class="p-3 text-center">
            <h4>Directory Tree View</h4>
        </div>
        <div class="container d-none border rounded" id="list_js">
            <button class="btn p-2 m-4" onclick="return(window.location.reload())"><i class="fas fa-times"></i></button>
            <ul class="p-3" id="listing"></ul>
        </div>
        <div class="main_list border rounded container" id="treeView">
            <?php if ($result) : ?>
                <form action="" method="post">
                    <button type="submit" class="btn p-2 m-4"><i class="fas fa-times"></i></button>
                </form>
                <div class="d-flex justify-content-around align-items-center">
                    <div>
                        <p class="m-1">Search the name of your folder</p>
                        <div class="search_parent">
                            <input class="form-control bg-gradient form-control-sm" id="searchInput" onkeyup="searchFunc()" type="search" autocomplete="off" placeholder="Search" aria-label="Search">
                            <label for="searchInput"><span class="sicon_list"><i class="fa-solid fa-magnifying-glass"></i></span></label>
                        </div>
                    </div>
                    <button class="btn btn-outline-info p-2 m-4" id="showFile">Show all files</button>
                    <?php if ($extension) : ?>
                        <div id="myBtnContainer" class="mb-3">
                            <label for="filterSelect">Filter by:</label>
                            <select id="filterSelect" class="form-select" onchange="filterSelection(this.value)">
                                <option value="all">Extension</option>
                                <?php foreach ($extension as $key => $value) : ?>
                                    <option value="<?= $value ?>"><?= $value ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="treeview p-3">
                    <?php
                    function generateTreeView($data, $level = 0)
                    {
                        $tab = str_repeat(" ", $level);
                    ?>
                        <?php if ($level > 0) : ?>
                            <?= $tab ?><ul class="nested">
                            <?php endif; ?>
                            <?php foreach ($data as $item) : ?>
                                <li class="treeview_item">
                                    <?php if (isset($item['NomFich'])) : ?>
                                        <div class="d-flex">
                                            <h6 class="nomFichier"><?= $item['NomFich'] ?> <i class="far fa-file me-2"></i></h6>
                                            <h7>/ size (<?= $item['Taille'] ?>)</h7>
                                            <h7 class="">/ extension : <?= $item['Ext'] ?></h7>
                                        </div>
                                    <?php elseif (isset($item['NomDoss'])) : ?>
                                        <span class="caret"><i class="far fa-folder me-2"></i> <?= $item['NomDoss'] ?></span>
                                        <?php if (isset($item['Children'])) {
                                            generateTreeView($item['Children'], $level + 1);
                                        } ?>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                            <?php if ($level > 0) : ?>
                                <?= $tab ?>
                            </ul>
                        <?php endif; ?>
                    <?php }; ?>
                    <?php generateTreeView($result); ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
    <script>
        function searchFunc() {
            var input, filter, table, elements;
            input = document.getElementById("searchInput");
            filter = input.value.toUpperCase();
            table = document.querySelector(".treeview");
            if (table) {
                elements = table.querySelectorAll(".treeview_item");
                searchRecursive(elements, filter);
            }
        }

        function searchRecursive(elements, filter) {
            elements.forEach(function(element) {
                var txtValue = element.textContent || element.innerText;
                var caret = element.querySelectorAll(".caret");
                var caretDown = element.querySelectorAll(".caret-down");
                var nested = element.querySelectorAll(".nested");
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    element.style.display = "";
                    // element.classList.add("active");
                    if (caret) {
                        for (let j = 0; j < nested.length; j++) {
                            nested[j].classList.add("active");
                            caret[j].classList.add("caret-down");
                        }
                    }
                } else {
                    element.style.display = "none";
                    // if (caretDown) {
                    //     for (let j = 0; j < nested.length; j++) {
                    //         nested[j].classList.remove("active");
                    //         caretDown[j].classList.remove("caret-down");
                    //     }
                    // }
                }
                // Si l'élément a une classe spécifique indiquant qu'il représente un dossier
                if (element.querySelector(".caret")) {
                    // Appeler la fonction de recherche récursivement pour les éléments enfants
                    var nestedElements = element.querySelectorAll(".treeview_item");
                    searchRecursive(nestedElements, filter);
                }
            });
        }
        // function searchFunc() {
        //     var input, filter, table, h6, i, txtValue;
        //     input = document.getElementById("searchInput");
        //     filter = input.value.toUpperCase();
        //     table = document.getElementsByClassName("main_list")[0];
        //     var nestedElements = document.querySelectorAll(".nested");
        //     var caret = document.querySelectorAll(".caret");
        //     if (table) {
        //         h6 = table.querySelectorAll("h6");
        //         // console.log("h6 length", h6);
        //         for (i = 0; i < h6.length; i++) {
        //             txtValue = h6[i].textContent || h6[i].innerText;
        //             // console.log("textValue", txtValue);
        //             // console.log("filter index of", txtValue.toUpperCase().indexOf(filter) > -1);
        //             if (txtValue.toUpperCase().indexOf(filter) > -1) {
        //                 h6[i].style.display = "";
        //                 nestedElements[i].classList.add("active");
        //                 caret[i].classList.add("caret-down");
        //             } else {
        //                 h6[i].style.display = "none";
        //                 nestedElements[i].classList.remove("active");
        //                 caret[i].classList.remove("caret-down");
        //             }
        //         }
        //     }
        // }
        // recuprer le chemin depuis input type file  
        document.getElementById("filepicker").addEventListener(
            "change",
            (event) => {
                let listJs = document.getElementById('list_js');
                listJs.className = listJs.className.replace('d-none', 'd-block');
                // console.log('on est entrer !');
                let output = document.getElementById("listing");
                let files = event.target.files;
                for (const file of files) {
                    displayFile(output, file, file.webkitRelativePath);
                }
            },
            false,
        );
        // il faut recuperer les / puis afficher sous tree view 
        function displayFile(parent, file, path, depth = null) {
            let item = document.createElement("li");
            let directory = file.isDirectory;
            let fileName = file.name;
            let lastModif = file.lastModifiedDate;
            let formattedDate;
            if (lastModif) {
                const day = lastModif.getDate();
                const month = lastModif.getMonth() + 1;
                const year = lastModif.getFullYear();
                // Get time components
                const hours = lastModif.getHours();
                const minutes = lastModif.getMinutes();
                const seconds = lastModif.getSeconds();
                formattedDate = `le : ${day}/${month}/${year} à : ${hours}:${minutes}:${seconds}`;
                // console.log('date formater : ', formattedDate);
            }
            // console.log("s name", fileName);
            // console.log("s modifié le : ", lastModif);
            // console.log("s directory", directory);
            // console.log("s parent", parent);
            // console.log("s file", file);
            // console.log("s depth", depth);
            // console.log("s path", path);
            let span = document.createElement("span");
            // Add caret class for toggle
            span.classList.add("caret");
            span.textContent = path;
            if (directory) {
                let subList = document.createElement("ul");
                // Add nested class for styling
                subList.classList.add("nested");
                item.appendChild(span);
                item.appendChild(subList);
                if (file && directory) {
                    let directoryReader = file.createReader();
                    directoryReader.readEntries((entries) => {
                        for (const entry of entries) {
                            displayFile(subList, entry, entry.name, depth + 1);
                        }
                    });
                }
            } else {
                item.appendChild(span);
                item.textContent += " (" + formatBytes(file.size) + ") Modifié " + formattedDate;
            }
            // Add depth-based indentation
            item.style.marginLeft = depth * 20 + 'px';
            parent.appendChild(item);
        }
        // formater le chiffre en bytes !
        function formatBytes(bytes, decimals = 2) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const dm = decimals < 0 ? 0 : decimals;
            const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
        }
        var toggler = document.getElementsByClassName("caret");
        var i;
        for (i = 0; i < toggler.length; i++) {
            toggler[i].addEventListener("click", function() {
                this.parentElement.querySelector(".nested").classList.toggle("active");
                this.classList.toggle("caret-down");
            });
        }
        //  show all sub repos (files)
        var showBtn = document.getElementById('showFile');
        if (showBtn) showBtn.addEventListener('click', function() {
            // console.log('clicked !');
            var nestedElements = document.querySelectorAll(".nested");
            var caret = document.querySelectorAll(".caret");
            if (showBtn.textContent === 'Show all files') {
                showBtn.textContent = 'Hide all files';
                for (var i = 0; i < nestedElements.length; i++) {
                    nestedElements[i].classList.add("active");
                    caret[i].classList.add("caret-down");
                }
            } else {
                showBtn.textContent = 'Show all files';
                for (var i = 0; i < nestedElements.length; i++) {
                    nestedElements[i].classList.remove("active");
                    caret[i].classList.remove("caret-down");
                }
            }
        })
        // mettre le variable $resultat en array vide 
        // document.getElementById('clearButton').addEventListener('click', function() {
        //     // Utiliser AJAX pour envoyer une requête au serveur PHP
        //     var xhr = new XMLHttpRequest();
        //     xhr.onreadystatechange = function() {
        //         if (xhr.readyState == 4 && xhr.status == 200) {
        //             // Mettre à jour la page ou effectuer d'autres actions si nécessaire
        //             console.log(xhr.responseText);
        //         }
        //     };
        //     // Envoyer une requête POST au fichier PHP avec l'action à effectuer
        //     xhr.open('POST', 'chemin_info.php', true);
        //     xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        //     xhr.send('clearArray=true');
        // });

        // input de search version 2
        // function searchFunc() {
        //     var input, filter, treeView, h6, i, txtValue;
        //     input = document.getElementById("searchInput");
        //     filter = input.value.toUpperCase();
        //     treeView = document.getElementsByClassName("main_list")[0]; // Utilisez la classe pour cibler l'élément
        //     if (treeView) {
        //         h6 = treeView.querySelectorAll("h6"); // Sélectionnez tous les éléments <h6> dans la structure d'arborescence
        //         for (i = 0; i < h6.length; i++) {
        //             txtValue = h6[i].textContent || h6[i].innerText;
        //             if (txtValue.toUpperCase().indexOf(filter) > -1) {
        //                 h6[i].closest('li').style.display = "";
        //             } else {
        //                 h6[i].closest('li').style.display = "none";
        //             }
        //         }
        //     }
        // }

        // filter par extension !!!
        // filterSelection("all")
        var extension = <?= json_encode($extension); ?>;
        console.log(extension);

        function filterSelection(c) {
            console.log("value cliqué", c);
            var x, i;
            x = document.getElementsByClassName("nested");
            // var nestedElements = document.querySelectorAll(".nested");
            // console.log("x is here ", x);
            var caret = document.querySelectorAll(".caret");
            if (c == "all") c = "";
            // Add the "show" class (display:block) to the filtered elements, and remove the "show" class from the elements that are not selected
            for (i = 0; i < x.length; i++) {
                elemRemoveClass(x[i], "show");
                if (x[i].className.indexOf(c) > -1) elAddClass(x[i], "show");
            }
        }

        // Show filtered elements
        function elAddClass(element, name) {
            var i, arr1, arr2;
            arr1 = element.className.split(" ");
            arr2 = name.split(" ");
            for (i = 0; i < arr2.length; i++) {
                if (arr1.indexOf(arr2[i]) == -1) {
                    element.className += " " + arr2[i];
                }
            }
        }

        // Hide elements that are not selected
        function elemRemoveClass(element, name) {
            var i, arr1, arr2;
            arr1 = element.className.split(" ");
            arr2 = name.split(" ");
            for (i = 0; i < arr2.length; i++) {
                while (arr1.indexOf(arr2[i]) > -1) {
                    arr1.splice(arr1.indexOf(arr2[i]), 1);
                }
            }
            element.className = arr1.join(" ");
        }

        // Add active class to the current control button (highlight it)
        // var btnContainer = document.getElementById("myBtnContainer");
        // var btns = btnContainer.getElementsByClassName("btn");
        // for (var i = 0; i < btns.length; i++) {
        //     btns[i].addEventListener("click", function() {
        //         var current = document.getElementsByClassName("active");
        //         current[0].className = current[0].className.replace(" active", "");
        //         this.className += " active";
        //     });
        // }
    </script>
</body>

</html>