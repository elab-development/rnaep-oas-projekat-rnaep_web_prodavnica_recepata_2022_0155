import { useMemo, useState } from "react";
import { useNavigate } from "react-router-dom";
import "../styles/recipes.css";

import { useIngredients } from "../hooks/useIngredients";
import { useRecipesList } from "../hooks/useRecipesList";
import { idsToCsv, toggleId, removeId } from "../utils/recipesHelpers";

import RecipesFiltersPanel from "../components/recipes/RecipesFiltersPanel";
import RecipesTable from "../components/recipes/RecipesTable";
import RecipesPager from "../components/recipes/RecipesPager";

function RecipesPage() {
  const navigate = useNavigate();

  const [search, setSearch] = useState("");
  const [sort, setSort] = useState("name");
  const [perPage, setPerPage] = useState(15);
  const [page, setPage] = useState(1);

  const { ingredients: allIngredients, loading: ingredientsLoading, error: ingredientsError } =
    useIngredients();

  const [anyIds, setAnyIds] = useState([]);
  const [allIds, setAllIds] = useState([]);
  const [excludeIds, setExcludeIds] = useState([]);

  const ingredientById = useMemo(() => {
    const m = new Map();
    for (const ing of allIngredients) m.set(ing.id, ing);
    return m;
  }, [allIngredients]);

  const ingredientLabel = (ing) => {
    if (!ing) return "Unknown";
    return `${ing.name} (${ing.unit}, ${ing.price})`;
  };

  const removeFromAny = (id) => {
    setAnyIds((prev) => removeId(prev, id));
    setPage(1);
  };
  const removeFromAll = (id) => {
    setAllIds((prev) => removeId(prev, id));
    setPage(1);
  };
  const removeFromExclude = (id) => {
    setExcludeIds((prev) => removeId(prev, id));
    setPage(1);
  };

  const params = useMemo(() => {
    const p = { sort, per_page: perPage, page };

    const s = search.trim();
    if (s) p.search = s;

    const csvAny = idsToCsv(anyIds);
    const csvAll = idsToCsv(allIds);
    const csvEx = idsToCsv(excludeIds);

    if (csvAny) p.ingredients_any = csvAny;
    if (csvAll) p.ingredients_all = csvAll;
    if (csvEx) p.ingredients_exclude = csvEx;

    return p;
  }, [search, sort, perPage, page, anyIds, allIds, excludeIds]);

  const { recipes, meta, loading, error } = useRecipesList(params, perPage);

  function slugify(s = "") {
    return s
      .toString()
      .toLowerCase()
      .trim()
      .replace(/[^\p{L}\p{N}]+/gu, "-") 
      .replace(/(^-|-$)+/g, "");
  }
 const openDetails = (recipe) => {
  const id = recipe?.id ?? recipe?.recipe_id;
  const name = recipe?.name ?? recipe?.title ?? "recept";
  const slug = slugify(name);

  navigate(`/recipes/${id}/${slug}`);
};

  const reset = () => {
    setSearch("");
    setSort("name");
    setPerPage(15);
    setPage(1);
    setAnyIds([]);
    setAllIds([]);
    setExcludeIds([]);
  };

  return (
    <div className="recipes-page">
      <h2 className="recipes-title">Recepti</h2>

      {error && <div className="alert alert-error">{error}</div>}
      {loading && <div className="alert alert-info">Učitavanje recepata...</div>}

      {ingredientsError && <div className="alert alert-error">{ingredientsError}</div>}
      {ingredientsLoading && <div className="alert alert-info">Učitavanje sastojaka...</div>}

      <RecipesFiltersPanel
        search={search}
        setSearch={setSearch}
        sort={sort}
        setSort={setSort}
        perPage={perPage}
        setPerPage={setPerPage}
        setPage={setPage}
        reset={reset}
        allIngredients={allIngredients}
        ingredientById={ingredientById}
        ingredientLabel={ingredientLabel}
        anyIds={anyIds}
        setAnyIds={setAnyIds}
        removeFromAny={removeFromAny}
        allIds={allIds}
        setAllIds={setAllIds}
        removeFromAll={removeFromAll}
        excludeIds={excludeIds}
        setExcludeIds={setExcludeIds}
        removeFromExclude={removeFromExclude}
        toggleId={toggleId}
      />

      <RecipesTable recipes={recipes} loading={loading} onOpenDetails={openDetails} />

      <RecipesPager
        meta={meta}
        onPrev={() => setPage((p) => Math.max(1, p - 1))}
        onNext={() => setPage((p) => Math.min(meta?.last_page ?? 1, p + 1))}
      />
    </div>
  );
}

export default RecipesPage;