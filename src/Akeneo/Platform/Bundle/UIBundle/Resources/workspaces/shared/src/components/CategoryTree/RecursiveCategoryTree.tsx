import React from "react";
import { CategoryTreeModel } from "./CategoryTree";
import {Tree} from 'akeneo-design-system/lib/components/Tree/Tree';

type RecursiveCategoryTreeProps = {
  tree: CategoryTreeModel;
  childrenCallback: (value: any) => Promise<CategoryTreeModel[]>;
  onChange: (value: string, checked: boolean) => void;
}

type CategoryTreeValue = {
  id: number;
  code: string;
}

const RecursiveCategoryTree: React.FC<RecursiveCategoryTreeProps> = ({
  tree,
  childrenCallback,
  onChange,
}) => {
  const [treeState, setTreeState] = React.useState<CategoryTreeModel>(tree);

  const handleOpen = () => {
    if (typeof treeState.children === 'undefined') {
      setTreeState({...treeState, loading: true});
      childrenCallback(treeState.id).then((children) => {
        setTreeState({...treeState, loading: false, children});
      });
    }
  }

  const handleChange = (value: CategoryTreeValue, checked: boolean) => {
    setTreeState({ ...treeState, selected: checked });
    onChange(value.code, checked);
  }

  return <Tree<CategoryTreeValue>
    label={treeState.label}
    value={{
      id: treeState.id,
      code: treeState.code
    }}
    selected={treeState.selected}
    isLoading={treeState.loading}
    readOnly={treeState.readOnly}
    selectable={treeState.selectable}
    isLeaf={Array.isArray(treeState.children) && treeState.children.length == 0}
    onChange={handleChange}
    onOpen={handleOpen}
  >
    {treeState.children &&
    treeState.children.map(childNode => {
      return (
        <RecursiveCategoryTree
          key={childNode.id}
          tree={childNode}
          onChange={onChange}
          childrenCallback={childrenCallback}/>
        )
      })
    }
  </Tree>
}

export {RecursiveCategoryTree};