# WODA 

## Conventions

Ces conventions mentionnent parfois l'identifiant des issues ; il s'agit du dernier nombre indiqué dans leurs urls.

Je les ai écrites d'après mon expérience : il s'agit pour la plupart de conventions utilisée à mon travail, et je pense que ça aide beaucoup.

### Conventions de codage

Les conventions de codage doivent être calqués sur les documents suivants :

- [Nommage](http://symfony.com/doc/current/contributing/code/conventions.html)
- [PHP](http://symfony.com/doc/current/contributing/code/standards.html)
- [HTML/CSS](google-styleguide.googlecode.com/svn/trunk/htmlcssguide.xml)

### Généralité sur les commits

- Les messages de commits doivent être en français, et dire ce que fait le commit en question. Ceci signifie que le verbe est conjugé à la troisième personne (`Retire le README.md` plutôt que `Retrait du README.md`).

- Chaque message de commit lié à une issue (feature ou bug) doit obligatoirement être préfixé par `#<id de l'issue> - `. Par exemple, `#7 - Ajout du controlleur de l'administration de la localisation`.

  Cela permet de voir la liste des commits reliés à une issue directement en lisant l'issue concernée.

### Process de développement des features

- Chaque feature doit être développée sur une branche à part. Le nom des branches est *EN ANGLAIS* et doit être standardisé ainsi : `<numéro de l'issue>-<nom de la feature>`. Par exemple, `5-admin-user-list`.

  Cela permet de sélectionner plus précisement les features que l'on souhaite intégrer dans le trunk, de ne pas dépendre de l'état du travail des autres (si la branche de X ne fonctionne pas, celle de Y reste utilisable), de pouvoir travailler sur plusieurs features simultanément, et de pouvoir voir rapidement l'état d'avancement des diverses features en cours.

- Lorsqu'une feature est terminée, la personne a qui elle a été assignée doit la faire passer en *waiting for review*, puis poster un commentaire mentionnant un autre développeur (sur github, la mention est faite en écrivant le nom d'utilisateur précédé du symbole '@', ex `@arcanis`).

- Si la feature possède des caractéristiques visuelles, ce qui sera probablement souvent le cas, l'issue doit également être marquée avec *front to be done*, indiquant que la branche doit être optimisé graphiquement par la personne en charge.

- Lorsqu'un développeur valide une code review, il peut alors passer l'issue en *to merge*. Le développeur référent de la webapp se chargera alors de merger la feature dans le tronc commun.

  Pour lui faciliter la tâche, il est préférable de régulièrement tenir à jour les branches vis-à-vis de master.

### Points à faire attention

- Pour créer une branche :

  `git checkout master && git checkout -b <nouvelle branche>`  

  Si vous ne vous mettez pas sur votre branche `master` avant de créer la nouvelle branche, cette dernière contiendra les commits de la branche sur laquelle vous étiez, ce qui ne doit pas être le cas.
