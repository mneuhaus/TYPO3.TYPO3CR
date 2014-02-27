Feature: Localization in workspaces
  In order to have a publish workflow for translated content
  As an API user of the content repository
  I need support for workspaces and node localization

  Background:
    Given I have the following nodes:
      | Path                 | Node Type                 | Properties        | Locale |
      | /sites               | unstructured              |                   | mul_ZZ |
      | /sites/neosdemotypo3 | TYPO3.Neos.NodeTypes:Page | {"title": "Home"} | mul_ZZ |

  @fixtures
  Scenario: Translate existing node in user workspace, get by path
    Given I have the following nodes:
      | Identifier                           | Path                         | Node Type                 | Properties              | Workspace | Locale |
      | fd5ba6e1-4313-b145-1004-dad2f1173a35 | /sites/neosdemotypo3/subpage | TYPO3.Neos.NodeTypes:Page | {"title": "Subpage"}    | live      | mul_ZZ |
      | fd5ba6e1-4313-b145-1004-dad2f1173a35 | /sites/neosdemotypo3/subpage | TYPO3.Neos.NodeTypes:Page | {"title": "Unterseite"} | user-demo | de_DE  |
    When I get a node by path "/sites/neosdemotypo3/subpage" with the following context:
      | Locales       | Workspace |
      | de_DE, mul_ZZ | user-demo |
    Then I should have one node
    And The node property "title" should be "Unterseite"
    When I get a node by path "/sites/neosdemotypo3/subpage" with the following context:
      | Locales       | Workspace |
      | de_DE, mul_ZZ | live      |
    Then I should have one node
    And The node property "title" should be "Subpage"
    When I get a node by path "/sites/neosdemotypo3/subpage" with the following context:
      | Locales       | Workspace |
      | en_US, mul_ZZ | user-demo |
    Then I should have one node
    And The node property "title" should be "Subpage"

  @fixtures
  Scenario: Translate existing node in user workspace, get child nodes
    Given I have the following nodes:
      | Identifier                           | Path                         | Node Type                 | Properties              | Workspace | Locale |
      | fd5ba6e1-4313-b145-1004-dad2f1173a35 | /sites/neosdemotypo3/subpage | TYPO3.Neos.NodeTypes:Page | {"title": "Subpage"}    | live      | mul_ZZ |
      | fd5ba6e1-4313-b145-1004-dad2f1173a35 | /sites/neosdemotypo3/subpage | TYPO3.Neos.NodeTypes:Page | {"title": "Unterseite"} | user-demo | de_DE  |
    When I get the child nodes of "/sites/neosdemotypo3" with filter "TYPO3.Neos:Document" and the following context:
      | Locales       | Workspace |
      | en_US, mul_ZZ | user-demo |
    Then I should have one node
    And The node property "title" should be "Subpage"
    When I get the child nodes of "/sites/neosdemotypo3" with filter "TYPO3.Neos:Document" and the following context:
      | Locales       | Workspace |
      | de_DE, mul_ZZ | user-demo |
    Then I should have one node
    And The node property "title" should be "Unterseite"

  @fixtures
  Scenario: Create localized node in user workspace
    Given I have the following nodes:
      | Path                         | Node Type                 | Properties              | Workspace | Locale |
      | /sites/neosdemotypo3/subpage | TYPO3.Neos.NodeTypes:Page | {"title": "Unterseite"} | user-demo | de_DE  |
    When I get a node by path "/sites/neosdemotypo3/subpage" with the following context:
      | Locales       | Workspace |
      | de_DE, mul_ZZ | user-demo |
    Then I should have one node
    And The node property "title" should be "Unterseite"
    When I get a node by path "/sites/neosdemotypo3/subpage" with the following context:
      | Locales       | Workspace |
      | de_DE, mul_ZZ | live      |
    Then I should have 0 nodes

  @fixtures
  Scenario: Workspace hierarchy overrules locale fallback
    Given I have the following nodes:
      | Identifier                           | Path                         | Node Type                 | Properties                  | Workspace | Locale |
      | fd5ba6e1-4313-b145-1004-dad2f1173a35 | /sites/neosdemotypo3/subpage | TYPO3.Neos.NodeTypes:Page | {"title": "Subpage"}        | user-demo | en_ZZ  |
      | fd5ba6e1-4313-b145-1004-dad2f1173a35 | /sites/neosdemotypo3/subpage | TYPO3.Neos.NodeTypes:Page | {"title": "Subpage for US"} | live      | en_US  |
    When I get a node by path "/sites/neosdemotypo3/subpage" with the following context:
      | Locales              | Workspace |
      | en_US, en_ZZ, mul_ZZ | user-demo |
    Then I should have one node
    And The node property "title" should be "Subpage"
    When I get a node by path "/sites/neosdemotypo3/subpage" with the following context:
      | Locales              | Workspace |
      | en_US, en_ZZ, mul_ZZ | live      |
    Then I should have one node
    And The node property "title" should be "Subpage for US"

    When I get the child nodes of "/sites/neosdemotypo3" with filter "TYPO3.Neos:Document" and the following context:
      | Locales              | Workspace |
      | en_US, en_ZZ, mul_ZZ | user-demo |
    Then I should have one node
    And The node property "title" should be "Subpage"

    When I get the child nodes of "/sites/neosdemotypo3" with filter "TYPO3.Neos:Document" and the following context:
      | Locales              | Workspace |
      | en_US, en_ZZ, mul_ZZ | live      |
    Then I should have one node
    And The node property "title" should be "Subpage for US"

  @fixtures
  Scenario: Translate existing node in user workspace, publish to live
    Given I have the following nodes:
      | Identifier                           | Path                         | Node Type                 | Properties              | Workspace | Locale |
      | fd5ba6e1-4313-b145-1004-dad2f1173a35 | /sites/neosdemotypo3/subpage | TYPO3.Neos.NodeTypes:Page | {"title": "Subpage"}    | live      | mul_ZZ |
      | fd5ba6e1-4313-b145-1004-dad2f1173a35 | /sites/neosdemotypo3/subpage | TYPO3.Neos.NodeTypes:Page | {"title": "Unterseite"} | user-demo | de_DE  |
    And I get a node by path "/sites/neosdemotypo3/subpage" with the following context:
      | Locales       | Workspace |
      | de_DE, mul_ZZ | user-demo |
    When I publish the node

    When I get a node by path "/sites/neosdemotypo3/subpage" with the following context:
      | Locales       | Workspace |
      | de_DE, mul_ZZ | live      |
    Then I should have one node
    And The node property "title" should be "Unterseite"

    When I get a node by path "/sites/neosdemotypo3/subpage" with the following context:
      | Locales       | Workspace |
      | en_US, mul_ZZ | live      |
    Then I should have one node
    And The node property "title" should be "Subpage"

  @fixtures
  Scenario: Update existing live node variant in user workspace, publish to live
    Given I have the following nodes:
      | Identifier                           | Path                         | Node Type                 | Properties               | Workspace | Locale |
      | fd5ba6e1-4313-b145-1004-dad2f1173a35 | /sites/neosdemotypo3/subpage | TYPO3.Neos.NodeTypes:Page | {"title": "Subpage"}     | live      | mul_ZZ |
      | fd5ba6e1-4313-b145-1004-dad2f1173a35 | /sites/neosdemotypo3/subpage | TYPO3.Neos.NodeTypes:Page | {"title": "Unternehmen"} | live      | de_DE  |
    And I get a node by path "/sites/neosdemotypo3/subpage" with the following context:
      | Locales       | Workspace |
      | de_DE, mul_ZZ | user-demo |
    And I set the node property "title" to "Firma"
    # FIXME We have to get the node again to have a clean context, after persistAll there will be duplicate Workspace instances otherwise
    And I get a node by path "/sites/neosdemotypo3/subpage" with the following context:
      | Locales       | Workspace |
      | de_DE, mul_ZZ | user-demo |
    When I publish the node

    When I get a node by path "/sites/neosdemotypo3/subpage" with the following context:
      | Locales       | Workspace |
      | de_DE, mul_ZZ | live      |
    Then I should have one node
    And The node property "title" should be "Firma"

    When I get a node by path "/sites/neosdemotypo3/subpage" with the following context:
      | Locales       | Workspace |
      | en_US, mul_ZZ | live      |
    Then I should have one node
    And The node property "title" should be "Subpage"

  @fixtures
  Scenario: Translate existing node with multiple live variants in user workspace, publish to live
    Given I have the following nodes:
      | Identifier                           | Path                         | Node Type                 | Properties               | Workspace | Locale |
      | fd5ba6e1-4313-b145-1004-dad2f1173a35 | /sites/neosdemotypo3/subpage | TYPO3.Neos.NodeTypes:Page | {"title": "Subpage de"}  | live      | de_ZZ  |
      | fd5ba6e1-4313-b145-1004-dad2f1173a35 | /sites/neosdemotypo3/subpage | TYPO3.Neos.NodeTypes:Page | {"title": "Subpage mul"} | live      | mul_ZZ |
      | fd5ba6e1-4313-b145-1004-dad2f1173a35 | /sites/neosdemotypo3/subpage | TYPO3.Neos.NodeTypes:Page | {"title": "Unterseite"}  | user-demo | de_DE  |
    And I get a node by path "/sites/neosdemotypo3/subpage" with the following context:
      | Locales              | Workspace |
      | de_DE, de_ZZ, mul_ZZ | user-demo |
    When I publish the node

    When I get a node by path "/sites/neosdemotypo3/subpage" with the following context:
      | Locales              | Workspace |
      | de_DE, de_ZZ, mul_ZZ | live      |
    Then I should have one node
    And The node property "title" should be "Unterseite"

    When I get a node by path "/sites/neosdemotypo3/subpage" with the following context:
      | Locales       | Workspace |
      | de_ZZ, mul_ZZ | live      |
    Then I should have one node
    And The node property "title" should be "Subpage de"

    When I get a node by path "/sites/neosdemotypo3/subpage" with the following context:
      | Locales       | Workspace |
      | en_ZZ, mul_ZZ | live      |
    Then I should have one node
    And The node property "title" should be "Subpage mul"
