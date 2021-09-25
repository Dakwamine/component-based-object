# Component based object

A simple starter class for component based objects.

This is used when flexibility is needed, for highly interactive components.

## Dependency solver system

From version 3, you have a new dependency solver system. It makes instantiation
easier.

You may declare dependencies by extending `getDependencyDefinitions()`.

Using the class constructor and the `new` keyword is highly discouraged to
prevent logical problems on instantiation.
Always use the methods to add or retrieve components.

You can set two types of dependencies:

- root: easily acccessed using static methods by any object.
- fellow: accessed by components on the same "level" (siblings).
  Rarely useful. Used for interdependent components or components without
  special relationships (root components are all fellows).

You can create sub-components, but they cannot be added during the
dependency solving step. They must be added after the instance is ready.

```php
<?php

use Dakwamine\Component\ComponentBasedObject;
use Dakwamine\Component\FellowDependencyDefinition;
use Dakwamine\Component\RootDependencyDefinition;

class SomeObject extends ComponentBasedObject
{
  /**
   * Ideally use type hints on interfaces, but this is not mandatory.
   * Using base / abstract classes is also fine.
   *
   * @var A\Class\NameInterface
   */
  private $depA;

  /**
   * @var B\Class\NameInterface
   */
  private $depB;

  public function getDependencyDefinitions(): array
  {
    // Reuse parent definitions.
    // We can also rewrite/replace them if needed.
    $deps = parent::getDependencyDefinitions();

    // Example of a mandatory fellow dependency.
    // Throws an error on dependency processing
    // if no suitable dependency has been found.
    // Automatically sets $this->depA with the 
    // instantiated dependency (passed by reference).
    $dependencyA = new FellowDependencyDefinition('A\Class\Name', $this->depA);
    $deps[] = $dependencyA;

    // Example of a fellow dependency, which transforms to root
    // dependency as a backup method.
    $dependencyB = new FellowDependencyDefinition('B\Class\Name', $this->depB);
    $deps[] = $dependencyB;

    // You can set backups like this.
    $dependencyB
      ->setBackupDependency(new RootDependencyDefinition('Missing\Class\Name', $this->depB))
      ->setBackupDependency(new RootDependencyDefinition(AnotherComponentBasedObject::class, $this->depB));

    return $deps;
  }
}
```

## Implementation example

This shows a simple implementation of the system where a **Company** has **Team**s which have **Member**s.

The **Company** needs a **LandManager** to establish.

### LandManager class

```php
<?php

use Dakwamine\Component\ComponentBasedObject;

class LandManager extends ComponentBasedObject {
  /**
   * @var Company[]
   */
  private $companies = [];

  public function getEstablishedCompanyByName(string $name): ?Company
  {
    foreach($this->companies as $company) {
      if ($company->name === $name) {
        return $company;
      }
    }

    return null;
  }

  public function provideLand(Company $company): void
  {
    if (in_array($company, $this->companies, true)) {
      // Already registered.
      return;
    }

    $this->companies[] = $company;
  }
}
```

### Company class

```php
<?php

use Dakwamine\Component\ComponentBasedObject;
use Dakwamine\Component\RootDependencyDefinition;

class Company extends ComponentBasedObject {
  /**
   * @var LandManager
   */
  private $landManager;

  /**
   * @var string
   */
  public $name;

  public function buildHeadquarters(): void
  {
    $this->landManager->provideLand($this);
  }

  public function getDependencyDefinitions(): array
  {
    $deps = parent::getDependencyDefinitions();
    $deps[] = new RootDependencyDefinition(LandManager::class, $this->landManager);
    return $deps;
  }

  public function getTeamByType(string $teamType): ?Team
  {
    foreach($this->getComponentsByClassName(Team::class) as $team) {
      if ($team->type === $$teamType) {
        return $team;
      }
    }

    return null;
  }
}
```

### Team class

```php
<?php

use Dakwamine\Component\ComponentBasedObject;

class Team extends ComponentBasedObject {
  /**
   * @var string
   */
  public $type;

  public function getMemberInfo(): array
  {
    $names = [];

    foreach ($this->getComponentsByClassName(Member::class) as $member) {
      $names[] = $member->name . ' (team: ' . $this->type . ')';
    }

    return $names;
  }
}
```

### Member class

```php
<?php

use Dakwamine\Component\ComponentBasedObject;

class Member extends ComponentBasedObject {
  /**
   * @var string
   */
  public $name;
}
```

### index.php (main script)

```php
<?php

use Dakwamine\Component\ComponentBasedObject;

// Instantiate a company.
$company = ComponentBaseObject::getRootComponentByClassName(Company::class);
$company->setName('Some Agency');
$company->buildHeadquarters();

// Add a management team to the company.
$managementTeam = $company->addComponentByClassName(Team::class);
$managementTeam->type = 'management';

// Add members to the management team.
$member = $managementTeam->addComponentByClassName(Member::class);
$member->name = 'Jane';
$member = $managementTeam->addComponentByClassName(Member::class);
$member->name = 'Bart';

// Add a dev team to the company.
$devTeam = $company->addComponentByClassName(Team::class);
$devTeam->type = 'dev';

// Add members to the dev team.
$member = $devTeam->addComponentByClassName(Member::class);
$member->name = 'Dakwamine';
$member = $devTeam->addComponentByClassName(Member::class);
$member->name = 'Saitama';

// Get member info starting from the LandManager
// (which was automatically instantiated because it is a dependency of the Company class).
// Note: it is better to check return values instead of chaining like this,
// or use null-safe operator (PHP 8).
$memberInfo = ComponentBasedObject::getRootComponentByClassName(LandManager::class)
  ->getCompanyByName('Some Agency')
  ->getTeamByType('management')
  ->getMemberInfo();

// Dump the member info variable.
var_dump($memberInfo);
```
