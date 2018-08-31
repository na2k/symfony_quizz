<?php
namespace App\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Category;
use App\Entity\Difficulty;
use App\Entity\Question;
use App\Entity\Answer;
class ApiController extends Controller
{
    /**
     * @Route("/api", name="api")
     */
    public function index()
    {
      $students = array(
        array('name' => 'Dominique', 'expertIn' => 'Angular'),
        array('name' => 'Tristan', 'expertIn' => 'Symfony'),
        array('name' => 'Younes', 'expertIn' => 'DragAndDrop')
      );
      // conversion en chaîne de caractères (JSON);
      $students_json = json_encode($students);
      $res = new Response();
      $res->headers->set('Content-Type', 'application/json');
      // ajout de l'entête autorisant les requêtes depuis
      // depuis une origine différente (ex: localhost:4200 vers
      // localhost:8000)
      $res->headers->set('Access-Control-Allow-Origin', '*');
      $res->setContent($students_json);
      return $res;
      //return new Response($students_json);
    }
    /**
     * @Route("/api/category", name="api_category")
     */
    public function category()
    {
      $categoryRepo =
        $this->getDoctrine()->getRepository(Category::class);
      $categories = $categoryRepo->findBy([], ['label' => 'ASC']);
      // transformation des objets en tableaux associatifs
      // de manière à pouvoir les convertir facilement en JSON
      $categories_assoc = [];
      foreach($categories as $category) {
        $category_assoc = array(
          'id' => $category->getId(),
          'label' => $category->getLabel()
        );
        array_push($categories_assoc, $category_assoc);
      }
      return $this->json_response($categories_assoc);
    }
    /**
     * @Route("/api/difficulty", name="api_difficulty")
     */
    public function difficulty()
    {
      $difficultyRepo = $this->getDoctrine()
        ->getRepository(Difficulty::class);
      $difficulties = $difficultyRepo->findBy([], ['weight' => 'ASC']);
      $difficulties_assoc = [];
      foreach($difficulties as $difficulty) {
        $difficulty_assoc = array(
          'id' => $difficulty->getId(),
          'label' => $difficulty->getLabel()
        );
      array_push($difficulties_assoc, $difficulty_assoc);
      }
      return $this->json_response($difficulties_assoc);
    }
    /**
     * @Route("/api/quizz", name="api_quizz")
     */
    public function quizz(Request $request)
    {
      // récupération des paramètres d'URL
      // ->query donne accès à la superglobale $_GET
      $category_id      = intval($request->query->get('cat'));
      $difficulty_id    = intval($request->query->get('dif'));
      $nb_questions     = intval($request->query->get('nbq'));
      $filters = []; // par défaut pas de filtre
      // si les critères sont différents de 0, on ajoute de
      // nouvelles paires clé => valeur au tableau des filtres
      if ($category_id != 0) $filters['category'] = $category_id;
      if ($difficulty_id != 0) $filters['difficulty'] = $difficulty_id;
      $questionRepo = $this->getDoctrine()->getRepository(Question::class);
      $questions = $questionRepo->findBy($filters, [], $nb_questions);
      if ($questions) {
        //return $this->json_response(['question0' => $questions[0]->getLabel()]);
        $qcm = [];
        foreach($questions as $question) {
          $question = [
            'id' => $question->getId(),
            'label' => $question->getLabel(),
            'choices' => $this->getChoices($question)
          ];
          array_push($qcm, $question);
        }
        shuffle($qcm); // mélange les éléments du tableau de manière aléatoire
        return $this->json_response($qcm);
      } else {
        return $this->json_response(['qcm' => 'aucune question']);
      }
    }
    /**
     * @Route("/api/quizz/answers", name="api_quizz_answers")
     */
    public function quizz_answers(Request $request)
    {
      // on reçoit une requête ajax
      // ->getContent permet d'obtenir le corps (body)
      // de la reqûete, cad les données envoyées
      $answers_json = $request->getContent(); // renvoie JSON
      $answers = json_decode($answers_json, $assoc = true); // JSON => PHP
      $result = $this->checkAnswers($answers['answers']);
      return $this->json_response(['result' => $result]);
    }
    /**
     * @Route("/api/test", name="api_test")
     */
    public function test()
    {
      return new Response('test');
    }
    // helpers
    private function json_response(Array $data)
    {
      $data_json = json_encode($data);
      $res = new Response();
      $res->headers->set('Content-Type', 'application/json');
      $res->headers->set('Access-Control-Allow-Origin', '*');
      //$res->headers->set('Access-Control-Allow-Methods', '*');
      $res->headers->set('Access-Control-Allow-Headers', 'Content-Type');
      $res->setContent($data_json);
      return $res;
    }
    private function getChoices($question)
    {
      $choices = [];
      foreach($question->getAnswers() as $answer) {
        $choice = [
          'id' => $answer->getId(),
          'label' => $answer->getLabel()
        ];
        array_push($choices, $choice);
        shuffle($choices);
      }
      return $choices;
    }
    private function checkAnswers(Array $client_answers)
    {
      $result = 0;
      $questionRepo = $this->getDoctrine()
        ->getRepository(Question::class);
      foreach($client_answers as $client_answer) {
        // obtenion de l'objet Question à partir de l'id
        $question = $questionRepo
          ->findOneBy(
            ['id' => intval($client_answer['qid'])]
          );
        // Récupération des réponses associées à la question
        $answers = $question->getAnswers();
        // itération sur les réponses associées à la question
        // si la réponse est juste, le résultat est incrémenté de 1
        for ($i=0; $i<count($answers); $i++) {
          if ($answers[$i]->getId() == $client_answer['aid']) {
            if ($answers[$i]->getGood()) $result++;
            break; // sortie prématurée de boucle,
            // inutile d'itérer sur les autres réponses
          }
        }
      } // fin foreach
      return $result;
    } // fin checkAnswers
}
