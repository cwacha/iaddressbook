# SVN 2 GIT migration of iaddressbook

## 1 Generate authors.txt
```
cd <old svn trunk>
svn log -q | awk -F '|' '/^r/ {sub("^ ", "", $2); sub(" $", "", $2); print $2" = "$2" <"$2">"}' | sort -u > authors-transform.txt
```

Edit authors to match github account

```
cat authors-transform.txt
```
> `reflex-2000 = Clemens Wacha <clemens@wacha.ch>`

## 2 Clone into GIT repo

```
mkdir iab
cd iab
git svn clone --stdlayout https://svn.code.sf.net/p/iaddressbook/code -A ../authors-transform.txt --no-metadata --prefix="" temp
```

 - `--stdlayout` can be used because I used folders (trunk, tags, branches)
 - `--no-metadata` can be used to prevent svn:id's in commit messages
 - `--prefix=""` should be used to get clean branches (without origin prefix). Everything else lateron depends on this setting!
 - Git version 2.25.0 was used

## 3 Migrate svn:ignore to .gitignore

```
cd temp
git svn show-ignore -i trunk
```

```
vi pkg/.gitignore
vi src/.gitignore
mkdir src/conf
vi src/conf/.gitignore
vi src/var/.gitignore
vi src/var/images/.gitignore
vi src/var/import/.gitignore
vi src/var/state/.gitignore
```

contents for `src/var/*/.gitignore` must be

```
*
!.gitignore
```

Split the results out into separate files. The `src/conf` folder must be created manually

```
git add *
git status 
Auf Branch master
Zum Commit vorgemerkte Änderungen:
  (benutzen Sie "git restore --staged <Datei>..." zum Entfernen aus der Staging-Area)
	neue Datei:     pkg/.gitignore
	neue Datei:     src/.gitignore
	neue Datei:     src/conf/.gitignore
	neue Datei:     var/.gitignore
	neue Datei:     var/images/.gitignore
	neue Datei:     var/import/.gitignore
	neue Datei:     var/state/.gitignore
```

```
git commit -m "converted svn:ignore to .gitignore"
cd ..
```

## 4 Push into bare repository
This step is required to get rid of svn clone settings that are no longer required. The special config setting will push all branches (except for the local master branch). Afterwards we have to push the `master` branch separately.

```
git init --bare new-bare.git
cd temp
git remote add bare ../new-bare.git
git config remote.bare.push 'refs/remotes/*:refs/heads/*'
git push bare
git push bare master
cd ..
```


## 5 Cleanup branches and tags
Tags have been created as branches, convert them into annotated git tags and remove the corresponding branch.

```
cd new-bare.git
git for-each-ref --format='%(refname)' refs/heads/tags |
cut -d / -f 4 |
while read ref
do
  git tag -a -m "Release $ref" "$ref" "refs/heads/tags/$ref";
  git branch -D "tags/$ref";
done
```

Now remove the `trunk` branch because it was replaced with `master`.
```
git branch -d trunk
cd ..
```

## 6 Create GITHUB repository
no explanation required

## 7 Move to GITHUB
In order to push all branches to github we have to check each one out locally. Otherwise only the `master` branch is pushed. Tags must be pushed separately.

```
git clone new-bare.git xxx
cd xxx
for branch in `git branch -r | grep -v master`; do git checkout --track $branch; done
git remote add github https://github.com/cwacha/iaddressbook.git
git push github --all
git push github --tags
```

DONE!!


